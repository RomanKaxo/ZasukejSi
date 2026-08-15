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
