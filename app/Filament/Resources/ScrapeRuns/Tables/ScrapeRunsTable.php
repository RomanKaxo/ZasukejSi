<?php

namespace App\Filament\Resources\ScrapeRuns\Tables;

use App\Filament\Resources\ScrapeItems\ScrapeItemResource;
use App\Filament\Resources\ScrapeSources\ScrapeSourceResource;
use App\Models\ScrapeRun;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ScrapeRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),

                TextColumn::make('source.name')->label('Zdroj')->badge()->sortable(),

                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        ScrapeRun::STATUS_COMPLETED => 'success',
                        ScrapeRun::STATUS_FAILED => 'danger',
                        ScrapeRun::STATUS_RUNNING => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('pages_fetched')->label('Stránek'),
                TextColumn::make('items_found')->label('Nalezeno'),
                TextColumn::make('items_new')->label('Nových')->color('success'),
                TextColumn::make('items_updated')->label('Změněných'),
                TextColumn::make('items_failed')->label('Chyb')->color('danger'),

                TextColumn::make('duration')
                    ->label('Trvání')
                    ->state(fn (ScrapeRun $record) => $record->durationSeconds() === null
                        ? '—'
                        : $record->durationSeconds() . ' s'),

                TextColumn::make('started_at')->label('Spuštěno')->dateTime()->sortable(),

                TextColumn::make('error')
                    ->label('Chyba')
                    ->limit(40)
                    ->color('danger')
                    ->placeholder('—')
                    // Truncated at 40 characters with no way to read the rest.
                    ->tooltip(fn (ScrapeRun $record) => $record->error)
                    ->toggleable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('scrape_source_id')->label('Zdroj')->relationship('source', 'name'),
                SelectFilter::make('status')->label('Stav')->options([
                    ScrapeRun::STATUS_COMPLETED => 'Dokončeno',
                    ScrapeRun::STATUS_FAILED => 'Selhalo',
                    ScrapeRun::STATUS_RUNNING => 'Běží',
                ]),
            ])
            ->recordActions([
                // Every run narrates what it did; that narration used to reach
                // only the console command.
                Action::make('log')
                    ->label('Průběh')
                    ->icon('heroicon-o-document-text')
                    ->modalHeading(fn (ScrapeRun $record) => 'Průběh běhu #' . $record->id)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Zavřít')
                    ->modalContent(fn (ScrapeRun $record) => view('filament.scraping.run-log', [
                        'run' => $record,
                    ]))
                    ->visible(fn (ScrapeRun $record) => filled($record->log)),

                // A run reported counts and nothing else — the rows it produced
                // were three screens and a filter away.
                Action::make('items')
                    ->label('Zobrazit položky')
                    ->icon('heroicon-o-inbox-stack')
                    ->url(fn (ScrapeRun $record) => ScrapeItemResource::getUrl('index', [
                        'tableFilters' => [
                            'scrape_run_id' => ['value' => $record->id],
                            // The run's own rows, whatever state they reached.
                            'status' => ['value' => null],
                        ],
                    ]))
                    ->visible(fn (ScrapeRun $record) => $record->items()->exists()),

                Action::make('rerun')
                    ->label('Spustit zdroj znovu')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->url(fn (ScrapeRun $record) => $record->scrape_source_id
                        ? ScrapeSourceResource::getUrl('edit', ['record' => $record->scrape_source_id])
                        : null)
                    ->visible(fn (ScrapeRun $record) => $record->status === ScrapeRun::STATUS_FAILED),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
