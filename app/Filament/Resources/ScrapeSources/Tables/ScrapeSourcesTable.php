<?php

namespace App\Filament\Resources\ScrapeSources\Tables;

use App\Filament\Resources\ScrapeRuns\ScrapeRunResource;
use App\Models\ScrapeRun;
use App\Models\ScrapeSource;
use App\Services\Scraping\ScrapeRunner;
use App\Services\Scraping\SourceConfig;
use Filament\Actions\Action;
// Filament 4 dropped `Filament\Notifications\Actions\Action`; notifications
// take the ordinary action class. Ten import shodil zkušební běh scraperu
// hláškou „Class ... not found" v okamžiku, kdy chtěl ohlásit výsledek.
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Throwable;

class ScrapeSourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Název')
                    ->description(fn (ScrapeSource $record) => $record->base_url)
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_enabled')
                    ->label('Zapnuto')
                    ->boolean(),

                TextColumn::make('adapter')
                    ->label('Adaptér')
                    ->badge(),

                TextColumn::make('field_maps_count')
                    ->label('Mapování')
                    ->counts('fieldMaps'),

                TextColumn::make('items_count')
                    ->label('Položek')
                    ->counts('items'),

                // Whether the source still works was only answerable by opening
                // the runs screen and filtering it.
                TextColumn::make('last_run')
                    ->label('Poslední běh')
                    ->state(fn (ScrapeSource $record) => $record->runs()->first()?->started_at?->diffForHumans() ?? 'nikdy')
                    ->badge()
                    ->color(fn (ScrapeSource $record) => match ($record->runs()->first()?->status) {
                        ScrapeRun::STATUS_COMPLETED => 'success',
                        ScrapeRun::STATUS_FAILED => 'danger',
                        ScrapeRun::STATUS_RUNNING => 'warning',
                        default => 'gray',
                    })
                    ->tooltip(fn (ScrapeSource $record) => $record->runs()->first()?->error),

                TextColumn::make('effective_delay')
                    ->label('Prodleva')
                    ->state(fn (ScrapeSource $record) => $record->effectiveCrawlDelay() . ' s')
                    ->tooltip('Vyšší z nastavení zdroje a hodnoty z robots.txt'),

                // Whether a source runs on its own, and when next.
                TextColumn::make('schedule')
                    ->label('Plán')
                    ->state(fn (ScrapeSource $record) => $record->isScheduled()
                        ? 'každých ' . $record->schedule_hours . ' h'
                        : 'ručně')
                    ->description(fn (ScrapeSource $record) => $record->isScheduled()
                        ? ($record->next_run_at?->format('d.m. H:i') ?? 'při nejbližší kontrole')
                        : null)
                    ->badge()
                    ->color(fn (ScrapeSource $record) => $record->isScheduled() ? 'success' : 'gray'),

                // Whether the source is still trusted to run on its own. A
                // site that had quietly started refusing us used to look
                // identical to one that was working.
                TextColumn::make('health')
                    ->label('Stav')
                    ->state(fn (ScrapeSource $record) => match (true) {
                        $record->isPaused() => 'pozastaveno',
                        $record->consecutive_failures > 0 => $record->consecutive_failures . '× selhalo',
                        $record->last_success_at !== null => 'v pořádku',
                        default => 'neběželo',
                    })
                    ->badge()
                    ->color(fn (ScrapeSource $record) => match (true) {
                        $record->isPaused() => 'danger',
                        $record->consecutive_failures > 0 => 'warning',
                        $record->last_success_at !== null => 'success',
                        default => 'gray',
                    })
                    ->tooltip(fn (ScrapeSource $record) => $record->paused_reason),

                TextColumn::make('robots_checked_at')
                    ->label('robots.txt')
                    ->since()
                    ->placeholder('nenačten'),
            ])
            ->recordActions([
                // Jestli nás web vůbec pustí. Bez tohohle se 403 dalo zjistit
                // jen spuštěním celého běhu, a odpověď „selhalo" nerozlišila
                // blokaci od špatného selektoru. Testuje se ze serveru, kde
                // na tom záleží — z vývojářského stroje ta samá adresa může
                // odpovídat úplně jinak.
                Action::make('testConnection')
                    ->label('Otestovat spojení')
                    ->icon('heroicon-o-signal')
                    ->color('gray')
                    ->form([
                        TextInput::make('url')
                            ->label('Adresa k otestování')
                            ->url()
                            ->helperText('Prázdné = domovská adresa zdroje.'),
                    ])
                    ->action(function (ScrapeSource $record, array $data) {
                        $url = $data['url'] ?: $record->base_url;

                        try {
                            $body = app(\App\Services\Scraping\HttpFetcher::class)->get($record, $url);

                            Notification::make()
                                ->title('Web odpověděl')
                                ->body(sprintf(
                                    'Staženo %s znaků. Odesláno jako „%s".',
                                    number_format(strlen($body), 0, ',', ' '),
                                    $record->setting('user_agent'),
                                ))
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Spojení selhalo')
                                ->body($e->getMessage() . ' Podrobnosti zjistí „Diagnostika spojení".')
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),

                // „HTTP 403 — web nás odmítl" je pravda a k ničemu. Neřekne,
                // jestli web odmítá adresu tohohle serveru, jméno našeho bota,
                // nebo všechno, co nevypadá jako prohlížeč — a každá z těch
                // tří věcí se řeší jinak. Tohle projde krátký žebřík pokusů a
                // řekne, na kterém příčku to prošlo.
                Action::make('diagnoseConnection')
                    ->label('Diagnostika spojení')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->form([
                        TextInput::make('url')
                            ->label('Adresa k prozkoumání')
                            ->url()
                            ->helperText('Prázdné = domovská adresa zdroje.'),
                    ])
                    ->modalSubmitActionLabel('Prozkoumat')
                    ->action(function (ScrapeSource $record, array $data) {
                        $report = app(\App\Services\Scraping\ConnectionDoctor::class)
                            ->diagnose($record, $data['url'] ?: null);

                        $lines = [];

                        foreach ($report['attempts'] as $attempt) {
                            $lines[] = sprintf(
                                '%s %s — %s%s',
                                $attempt['ok'] ? '✓' : '✕',
                                $attempt['label'],
                                $attempt['error'] ?? ('HTTP ' . $attempt['status']),
                                $attempt['protection'] ? ' [' . $attempt['protection'] . ']' : '',
                            );
                        }

                        $lines[] = '';
                        $lines[] = $report['verdict'];

                        if ($report['suggestion'] !== []) {
                            $lines[] = '';
                            $lines[] = 'Do nastavení zdroje uložte:';
                            $lines[] = 'user_agent = ' . $report['suggestion']['user_agent'];
                            $lines[] = 'headers = ' . $report['suggestion']['headers'];
                        }

                        Notification::make()
                            ->title('Diagnostika spojení')
                            ->body(implode("\n", $lines))
                            ->color($report['suggestion'] !== [] ? 'success' : 'warning')
                            ->persistent()
                            ->send();
                    }),

                // Poslední cesta, která funguje vždycky: web může odmítat
                // tenhle server a přitom se k němu člověk u obyčejného
                // prohlížeče dostane bez potíží. Tohle mu umožní stránku
                // uložit a předat ji dál — stejné selektory, stejné pojistky,
                // stejná fronta ke kontrole, jen bez stahování.
                Action::make('ingestHtml')
                    ->label('Vložit staženou stránku')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('gray')
                    ->modalHeading('Zpracovat stránku uloženou z prohlížeče')
                    ->modalDescription('V prohlížeči otevřete profil, uložte stránku (Ctrl+U → zkopírovat zdroj) a vložte ji sem. Adresa se použije pro identifikaci profilu — musí to být ta skutečná.')
                    ->modalSubmitActionLabel('Zpracovat')
                    ->form([
                        TextInput::make('url')
                            ->label('Adresa profilu')
                            ->url()
                            ->required()
                            ->helperText('Ta, ze které stránka pochází. Podle ní se pozná, jestli už profil máme.'),

                        \Filament\Forms\Components\Textarea::make('html')
                            ->label('Zdrojový kód stránky')
                            ->rows(10)
                            ->required()
                            ->helperText('Celé HTML. Fotky se stáhnou až při importu profilu, ty tedy web pouštět musí — pokud ne, profil vznikne bez nich.'),
                    ])
                    ->action(function (ScrapeSource $record, array $data) {
                        try {
                            $run = app(ScrapeRunner::class)->ingestHtml(
                                $record,
                                (string) $data['url'],
                                (string) $data['html'],
                            );
                        } catch (Throwable $e) {
                            Notification::make()->title('Zpracování selhalo')->body($e->getMessage())->danger()->send();

                            return;
                        }

                        if ($run->error) {
                            Notification::make()->title('Zpracování selhalo')->body($run->error)->danger()->persistent()->send();

                            return;
                        }

                        Notification::make()
                            ->title($run->items_new > 0 ? 'Profil přidán ke kontrole' : 'Profil aktualizován')
                            ->body("Nových {$run->items_new}, změněných {$run->items_updated}, chyb {$run->items_failed}. Průběh běhu #{$run->id} ukáže, co selektory vrátily.")
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                // Když web stojí za kontrolou prohlížeče, je renderer jediná
                // cesta, která nevyžaduje domluvu s provozovatelem. Ověřit ho
                // musí jít jedním klikem — ne spuštěním celé sklizně, u které
                // se pak hádá, jestli selhal renderer, nebo selektory.
                Action::make('testRenderer')
                    ->label('Otestovat renderer')
                    ->icon('heroicon-o-globe-alt')
                    ->color('gray')
                    // Viditelné vždycky. Schovávat ho, dokud není renderer
                    // nastavený, znamenalo, že ho nenajde právě ten, kdo ho
                    // hledá — tedy člověk, kterého web odmítá a chce vědět,
                    // jestli je tahle cesta vůbec k dispozici.
                    ->form([
                        TextInput::make('url')
                            ->label('Adresa k vykreslení')
                            ->url()
                            ->helperText('Prázdné = domovská adresa zdroje.'),
                    ])
                    ->action(function (ScrapeSource $record, array $data) {
                        if (blank($record->setting('render_endpoint'))) {
                            Notification::make()
                                ->title('Renderer není nastavený')
                                ->body('Doplňte ho v úpravě zdroje, sekce „Když nás web odmítá" → Vykreslovací služba. Je to adresa externí služby, která stránku otevře v prohlížeči a vrátí hotové HTML.')
                                ->warning()
                                ->persistent()
                                ->send();

                            return;
                        }

                        $url = $data['url'] ?: $record->base_url;

                        try {
                            $html = app(\App\Services\Scraping\HttpFetcher::class)->get($record, $url);
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Renderer neuspěl')
                                ->body($e->getMessage() . ' Zkontrolujte adresu služby v nastavení `render_endpoint`.')
                                ->danger()
                                ->persistent()
                                ->send();

                            return;
                        }

                        $embedded = new \App\Services\Scraping\EmbeddedJson();
                        $text = mb_strlen(trim(strip_tags($html)));

                        // Renderer, který vrátí tutéž prázdnou skořápku, je
                        // horší než žádný: tváří se, že funguje.
                        $stillEmpty = $embedded->looksClientRendered($html);

                        Notification::make()
                            ->title($stillEmpty ? 'Renderer odpověděl, ale stránka je pořád prázdná' : 'Renderer funguje')
                            ->body(sprintf(
                                'Staženo %s znaků, z toho %s znaků textu.%s',
                                number_format(mb_strlen($html), 0, ',', ' '),
                                number_format($text, 0, ',', ' '),
                                $stillEmpty
                                    ? ' Vypadá to, že služba JavaScript nespustila — obsah v HTML není. Zkuste u ní zapnout čekání na vykreslení.'
                                    : ' Teď má smysl vyzkoušet selektory v Dílně.',
                            ))
                            ->color($stillEmpty ? 'warning' : 'success')
                            ->persistent()
                            ->send();
                    }),

                // A one-off run from the admin, deliberately capped: this is
                // for checking the selectors, not for harvesting a whole site.
                Action::make('testRun')
                    ->label('Zkušební běh')
                    ->icon('heroicon-o-play')
                    ->form([
                        TextInput::make('url')
                            ->label('URL jednoho profilu')
                            ->url()
                            ->helperText('Prázdné = projde výpis podle nastavení zdroje.'),

                        TextInput::make('limit')
                            ->label('Maximálně profilů')
                            ->numeric()
                            ->default(3)
                            ->minValue(1)
                            ->maxValue(25),

                        Toggle::make('dry_run')
                            ->label('Jen zkouška, nic neukládat')
                            ->default(true),
                    ])
                    ->action(function (ScrapeSource $record, array $data) {
                        try {
                            $run = app(ScrapeRunner::class)->run($record, array_filter([
                                'url' => $data['url'] ?: null,
                                'limit' => (int) ($data['limit'] ?? 3),
                                'dry_run' => (bool) ($data['dry_run'] ?? true),
                            ]));

                            // The point of a test run is what it extracted, not
                            // how many rows it touched — the run's log holds
                            // the field values and is one click away.
                            Notification::make()
                                ->title('Běh dokončen')
                                ->body("Nalezeno {$run->items_found}, nových {$run->items_new}, změněných {$run->items_updated}, chyb {$run->items_failed}. Průběh běhu #{$run->id} ukáže, co selektory vrátily.")
                                ->success()
                                ->actions([
                                    Action::make('showRun')
                                        ->label('Zobrazit průběh')
                                        ->url(ScrapeRunResource::getUrl('index')),
                                ])
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Běh selhal')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // The full harvest: paste a listing address and pull what is on
                // it. Only reachable on an enabled source, and everything still
                // lands in the review queue.
                Action::make('crawlListing')
                    ->label('Stáhnout z odkazu')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->visible(fn (ScrapeSource $record) => $record->is_enabled)
                    ->form([
                        TextInput::make('listing_url')
                            ->label('Odkaz na výpis profilů')
                            ->url()
                            ->helperText('Prázdné = použije se listing_path z nastavení zdroje.'),

                        TextInput::make('pages')
                            ->label('Kolik stránek výpisu projít')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(200),

                        TextInput::make('limit')
                            ->label('Maximálně profilů (0 = bez omezení)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->requiresConfirmation()
                    ->modalDescription(fn (ScrapeSource $record) => sprintf(
                        'Mezi požadavky se čeká %.1f s, takže stahování trvá. Profily se uloží ke kontrole, nic se nepublikuje.',
                        $record->effectiveCrawlDelay(),
                    ))
                    ->action(function (ScrapeSource $record, array $data) {
                        $listing = $data['listing_url'] ?? null;

                        // Set in memory only — the override applies to this run
                        // and is not written back to the source.
                        if ($listing) {
                            $record->settings = array_merge(
                                $record->settings ?? [],
                                ['listing_url_override' => $listing],
                            );
                        }

                        try {
                            $run = app(ScrapeRunner::class)->run($record, array_filter([
                                'pages' => (int) ($data['pages'] ?? 1),
                                'limit' => (int) ($data['limit'] ?? 0) ?: null,
                            ]));

                            if ($run->error) {
                                Notification::make()->title('Běh selhal')->body($run->error)->danger()->send();

                                return;
                            }

                            Notification::make()
                                ->title('Staženo')
                                ->body("Nalezeno {$run->items_found}, nových {$run->items_new}, změněných {$run->items_updated}, chyb {$run->items_failed}. Čekají ke kontrole.")
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()->title('Běh selhal')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // Back in the plan without hunting through the form for it.
                Action::make('resume')
                    ->label('Zrušit pauzu')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (ScrapeSource $record) => $record->isPaused())
                    ->requiresConfirmation()
                    ->modalDescription(fn (ScrapeSource $record) => 'Zdroj byl pozastaven: ' . ($record->paused_reason ?: 'bez uvedeného důvodu'))
                    ->action(function (ScrapeSource $record) {
                        $record->resume();

                        Notification::make()
                            ->title('Zdroj je zase v plánu')
                            ->success()
                            ->send();
                    }),

                // Half a day of finding selectors, in a file you can carry to
                // the staging server or hand to somebody else.
                Action::make('exportConfig')
                    ->label('Stáhnout nastavení')
                    ->icon('heroicon-o-arrow-down-on-square')
                    ->color('gray')
                    ->action(function (ScrapeSource $record) {
                        $config = app(SourceConfig::class);

                        return response()->streamDownload(
                            fn () => print $config->toJson($record),
                            $config->filename($record),
                            ['Content-Type' => 'application/json'],
                        );
                    }),

                EditAction::make()->label('Upravit'),
                DeleteAction::make()->label('Smazat'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
