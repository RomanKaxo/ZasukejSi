<?php

namespace App\Filament\Resources\Segments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SegmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('segments.table.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label(__('segments.table.slug'))
                    ->searchable()
                    ->sortable(),

                ColorColumn::make('color')
                    ->label(__('segments.table.color')),

                // A segment nobody is in behaves like a segment that does not
                // exist; that was only discoverable by filtering the profiles.
                TextColumn::make('profiles_count')
                    ->label(__('segments.table.profiles'))
                    ->counts('profiles')
                    ->badge()
                    ->color(fn ($state) => (int) $state > 0 ? 'success' : 'gray')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label(__('segments.table.sort_order'))
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('segments.table.active'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('segments.table.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
