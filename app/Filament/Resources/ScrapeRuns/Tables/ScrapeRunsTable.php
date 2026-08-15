<?php

namespace App\Filament\Resources\ScrapeRuns\Tables;

use App\Models\ScrapeRun;
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

                TextColumn::make('error')->label('Chyba')->limit(40)->color('danger')->placeholder('—')->toggleable(),
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
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
