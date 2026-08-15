<?php

namespace App\Filament\Resources\ScrapeSources\Tables;

use App\Models\ScrapeSource;
use App\Services\Scraping\ScrapeRunner;
use Filament\Actions\Action;
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

                TextColumn::make('effective_delay')
                    ->label('Prodleva')
                    ->state(fn (ScrapeSource $record) => $record->effectiveCrawlDelay() . ' s')
                    ->tooltip('Vyšší z nastavení zdroje a hodnoty z robots.txt'),

                TextColumn::make('robots_checked_at')
                    ->label('robots.txt')
                    ->since()
                    ->placeholder('nenačten'),
            ])
            ->recordActions([
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

                            Notification::make()
                                ->title('Běh dokončen')
                                ->body("Nalezeno {$run->items_found}, nových {$run->items_new}, změněných {$run->items_updated}, chyb {$run->items_failed}.")
                                ->success()
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
