<?php

namespace App\Filament\Resources\ScrapeSources\Schemas;

use App\Models\ScrapeSource;
use App\Services\Scraping\AdapterRegistry;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ScrapeSourceForm
{
    /** @return array<int, string> */
    private static function hours(): array
    {
        $hours = [];

        foreach (range(0, 23) as $hour) {
            $hours[$hour] = sprintf('%02d:00', $hour);
        }

        return $hours;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Zdroj')
                ->schema([
                    TextInput::make('name')
                        ->label('Název')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),

                    TextInput::make('slug')
                        ->label('Identifikátor')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('Používá se v příkazu: php artisan scrape:run <identifikátor>'),

                    TextInput::make('base_url')
                        ->label('Základní URL')
                        ->url()
                        ->required()
                        ->helperText('Bez koncového lomítka, např. https://www.priklad.cz'),

                    Select::make('adapter')
                        ->label('Adaptér')
                        ->options(fn () => app(AdapterRegistry::class)->options())
                        ->default('generic')
                        ->required()
                        ->helperText('Generický adaptér zvládne běžný výpis s odkazy a stránkováním.'),

                    Toggle::make('is_enabled')
                        ->label('Zapnuto')
                        ->helperText('Vypnutý zdroj lze spustit jen s --dry-run.'),
                ])
                ->columns(2),

            Section::make('Automatické spouštění')
                ->description('Bez intervalu se zdroj spouští jen ručně, jak tomu bylo dosud. Vypnutý zdroj se nespustí ani s intervalem.')
                ->schema([
                    TextInput::make('schedule_hours')
                        ->label('Interval (hodiny)')
                        ->helperText('Prázdné = žádné automatické spouštění. 24 znamená jednou denně.')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(24 * 30),

                    DateTimePicker::make('next_run_at')
                        ->label('Další běh')
                        ->helperText('Prázdné a s vyplněným intervalem znamená „hned při nejbližší kontrole".')
                        ->seconds(false),

                    TextInput::make('schedule_pages')
                        ->label('Stránek výpisu na běh')
                        ->helperText('Prázdné = použije se max_pages z nastavení zdroje.')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(200),

                    TextInput::make('schedule_limit')
                        ->label('Maximálně profilů na běh')
                        ->helperText('Prázdné = bez omezení. Stahování čeká mezi požadavky, takže velký běh trvá.')
                        ->numeric()
                        ->minValue(1),

                    Select::make('run_window_from')
                        ->label('Stahovat od hodiny')
                        ->options(self::hours())
                        ->helperText('Prázdné = kdykoli. Stahovat cizí web v jeho nejrušnější hodinu je nezdvořilé i nejjistější cesta k blokaci.'),

                    Select::make('run_window_to')
                        ->label('Stahovat do hodiny')
                        ->options(self::hours())
                        ->helperText('Okno smí přecházet přes půlnoc — 22 až 6 je běžné nastavení.'),

                    Select::make('run_days')
                        ->label('Dny v týdnu')
                        ->multiple()
                        ->options([
                            1 => 'Pondělí',
                            2 => 'Úterý',
                            3 => 'Středa',
                            4 => 'Čtvrtek',
                            5 => 'Pátek',
                            6 => 'Sobota',
                            7 => 'Neděle',
                        ])
                        ->helperText('Prázdné = každý den.')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Jak se hledají profily')
                ->description('Odkud scraper bere adresy jednotlivých profilů. Sitemapa je nejspolehlivější — web v ní sám vypisuje všechno, co chce mít nalezené, včetně data poslední změny.')
                ->schema([
                    Select::make('discovery')
                        ->label('Zdroj adres')
                        ->options([
                            'listing' => 'Procházet výpis (odkazy na stránce)',
                            'sitemap' => 'Sitemapa webu',
                        ])
                        ->default('listing')
                        ->live()
                        ->helperText('Výpis se prochází podle selektoru odkazů. Sitemapa žádný selektor nepotřebuje.'),

                    TextInput::make('sitemap_url')
                        ->label('Adresa sitemapy')
                        ->url()
                        ->visible(fn ($get) => $get('discovery') === 'sitemap')
                        ->helperText('Prázdné = zkusí se robots.txt a pak /sitemap.xml a /sitemap_index.xml.'),

                    Toggle::make('sitemap_changed_only')
                        ->label('Jen to, co se změnilo')
                        ->default(true)
                        ->visible(fn ($get) => $get('discovery') === 'sitemap')
                        ->helperText('Používá datum poslední změny ze sitemapy. Z nočního běhu se tím stane pár profilů místo celého webu.'),

                    Select::make('pagination_mode')
                        ->label('Stránkování výpisu')
                        ->options([
                            'paged' => 'Podle čísla stránky (?page=2)',
                            'next_link' => 'Podle odkazu „další stránka"',
                        ])
                        ->default('paged')
                        ->live()
                        ->visible(fn ($get) => $get('discovery') !== 'sitemap')
                        ->helperText('Odkaz na další stránku se hodí tam, kde adresy nejdou spočítat.'),

                    TextInput::make('next_link_selector')
                        ->label('Selektor odkazu na další stránku')
                        ->visible(fn ($get) => $get('discovery') !== 'sitemap' && $get('pagination_mode') === 'next_link')
                        ->helperText('Prázdné = zkusí se a[rel=next] a link[rel=next], což pokrývá většinu webů.'),
                ])
                ->columns(2),

            Section::make('Spolehlivost a šetrnost')
                ->description('Nastavení, kvůli kterým scraper zbytečně nezatěžuje cizí web ani nás.')
                ->schema([
                    Toggle::make('conditional_requests')
                        ->label('Stahovat jen změněné stránky')
                        ->default(true)
                        ->helperText('Ptáme se webu „změnilo se to od minule?". Když odpoví, že ne, nestahuje se nic — ani fotky.'),

                    Toggle::make('auto_pause')
                        ->label('Pozastavit zdroj po opakovaném selhání')
                        ->default(true)
                        ->live()
                        ->helperText('Web, který nás odmítá, se ptaním každou hodinu nespraví. Ruční spuštění pauzu zase zruší.'),

                    TextInput::make('max_attempts')
                        ->label('Kolikrát zkusit stránku, která selhala')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(20)
                        ->default(5)
                        ->helperText('Odstup mezi pokusy roste: 15 minut, hodina, 6 hodin, den.'),

                    TextInput::make('failure_threshold')
                        ->label('Kolik selhání v řadě')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(50)
                        ->default(3)
                        ->visible(fn ($get) => (bool) $get('auto_pause')),

                    TextInput::make('proxy')
                        ->label('Proxy')
                        ->placeholder('http://uzivatel:heslo@proxy.example.com:8080')
                        ->helperText('Jen pro weby, které blokují adresu našeho serveru. Nastavuje se u zdroje, ne globálně.'),

                    Placeholder::make('health')
                        ->label('Stav zdroje')
                        ->content(function ($record) {
                            if (! $record) {
                                return 'Zatím neběželo.';
                            }

                            $lines = [];

                            $lines[] = $record->last_success_at
                                ? 'Naposledy úspěšně: ' . $record->last_success_at->format('j. n. Y H:i')
                                : 'Zatím ani jeden úspěšný běh.';

                            if ($record->consecutive_failures > 0) {
                                $lines[] = 'Selhání v řadě: ' . $record->consecutive_failures;
                            }

                            if ($record->isPaused()) {
                                $lines[] = 'POZASTAVENO ' . $record->paused_at->format('j. n. Y H:i')
                                    . ' — ' . ($record->paused_reason ?: 'bez uvedeného důvodu');
                            }

                            return implode(' · ', $lines);
                        })
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Co se nesmí dostat do fronty')
                ->description('Věková hranice platí vždycky a je v kódu. Tohle je všechno ostatní, co se u konkrétního webu ukáže jako potřeba.')
                ->schema([
                    TextInput::make('minimum_age')
                        ->label('Nejnižší věk')
                        ->numeric()
                        ->minValue(18)
                        ->maxValue(99)
                        ->default(18)
                        ->helperText('Zvýšit lze, snížit pod 18 ne — pojistka to vynucuje bez ohledu na tohle pole. Neuvedený věk se neblokuje, ten patří do revize.'),

                    TextInput::make('max_requests')
                        ->label('Strop požadavků na běh')
                        ->numeric()
                        ->minValue(0)
                        ->helperText('0 = bez omezení. Chrání před chybným stránkováním, které umí vyrobit nekonečný seznam adres.'),

                    Textarea::make('content_rules')
                        ->label('Vlastní pravidla')
                        ->rows(6)
                        ->columnSpanFull()
                        ->helperText(
                            'Jedno pravidlo na řádek, „pole operátor hodnota". Položka, která pravidlu vyhoví, se rovnou zamítne. '
                            . 'Operátory: ~ (odpovídá výrazu), !~ (neodpovídá), = , != , empty, not_empty. '
                            . 'Například: about_me ~ /whatsapp\s*\+\d{6,}/i — nebo city != Brno — nebo phone empty. '
                            . 'Řádek začínající # je poznámka. Nesrozumitelný řádek se přeskočí a řekne se to při uložení.'
                        )
                        ->rules([
                            fn () => function (string $attribute, $value, \Closure $fail) {
                                foreach (preg_split('/\r\n|\r|\n/', (string) $value) as $number => $line) {
                                    $line = trim($line);

                                    if ($line === '' || str_starts_with($line, '#')) {
                                        continue;
                                    }

                                    if (! preg_match('/^([a-z_][a-z0-9_]*)\s+(!~|~|!=|=|empty|not_empty)\s*(.*)$/i', $line, $m)) {
                                        $fail('Řádek ' . ($number + 1) . ' nedává smysl: „' . $line . '".');

                                        continue;
                                    }

                                    if (in_array($m[2], ['~', '!~'], true) && @preg_match(trim($m[3]), '') === false) {
                                        $fail('Řádek ' . ($number + 1) . ': „' . trim($m[3]) . '" není platný regulární výraz.');
                                    }
                                }
                            },
                        ]),
                ])
                ->columns(2),

            Section::make('Chování při stahování')
                ->description('Prodleva se nikdy nesníží pod hodnotu, kterou požaduje robots.txt daného webu.')
                ->schema([
                    KeyValue::make('settings')
                        ->label('Nastavení')
                        ->keyLabel('Klíč')
                        ->valueLabel('Hodnota')
                        ->default(ScrapeSource::DEFAULT_SETTINGS)
                        ->addActionLabel('Přidat položku')
                        ->helperText(
                            'crawl_delay (s), timeout (s), max_pages, listing_path, pagination_param, '
                            . 'detail_link_selector, detail_url_pattern, external_id_pattern, '
                            . 'image_selector, image_attribute, image_prefer_pattern, image_limit, respect_robots, '
                            . 'user_agent, headers. — Když web začne vracet 403, mění se user_agent nebo se přidá '
                            . 'headers jako JSON, třeba {"Referer":"https://www.example.com/"}. Klíče, které mají '
                            . 'vlastní pole v sekcích výše, se tu nezobrazují — nastavují se tam.'
                        )
                        ->columnSpanFull(),
                ]),

            Section::make('Poznámky a robots.txt')
                ->schema([
                    Textarea::make('notes')
                        ->label('Poznámky')
                        ->rows(3)
                        ->columnSpanFull(),

                    KeyValue::make('robots_rules')
                        ->label('Poslední načtený robots.txt')
                        ->disabled()
                        ->columnSpanFull(),
                ])
                ->collapsed(),
        ]);
    }
}
