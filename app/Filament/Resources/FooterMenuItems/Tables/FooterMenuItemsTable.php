<?php

namespace App\Filament\Resources\FooterMenuItems\Tables;

use App\Models\FooterMenuItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FooterMenuItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Popisek')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('target')
                    ->label('Cíl')
                    ->state(fn (FooterMenuItem $record) => $record->resolvedUrl() ?? '—')
                    ->description(fn (FooterMenuItem $record) => $record->page_id
                        ? 'Stránka: ' . ($record->page?->title ?? 'smazaná')
                        : 'Vlastní adresa')
                    // An item whose page vanished resolves to nothing and the
                    // footer leaves it out — say so here rather than in silence.
                    ->color(fn (FooterMenuItem $record) => $record->resolvedUrl() ? null : 'danger')
                    ->url(fn (FooterMenuItem $record) => $record->resolvedUrl())
                    ->openUrlInNewTab(),

                TextColumn::make('column')
                    ->label('Sloupec')
                    ->badge()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Pořadí')
                    ->sortable(),

                IconColumn::make('opens_in_new_tab')
                    ->label('Nový panel')
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('is_visible')
                    ->label('Zobrazeno')
                    ->boolean(),
            ])
            ->defaultSort('column')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('column')
                    ->label('Sloupec')
                    ->options(array_combine(FooterMenuItem::COLUMNS, FooterMenuItem::COLUMNS)),

                TernaryFilter::make('is_visible')->label('Zobrazeno'),
            ])
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make()
                    ->label('Duplikovat')
                    ->excludeAttributes(['created_at', 'updated_at']),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Patička zatím nemá vlastní menu')
            ->emptyStateDescription('Dokud tu není ani jedna položka, patička vypisuje stránky označené „Zobrazit v patičce". Jakmile přidáte první položku, řídí se patička tímto seznamem.');
    }
}
