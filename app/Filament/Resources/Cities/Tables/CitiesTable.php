<?php

namespace App\Filament\Resources\Cities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Název')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('country_code')
                    ->label('Země')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('admin_name')
                    ->label('Region/kraj')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('population')
                    ->label('Obyvatel')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('population', 'desc')
            ->filters([
                SelectFilter::make('country_code')
                    ->label('Země')
                    ->options(fn () => \App\Models\City::query()
                        ->distinct()
                        ->orderBy('country_code')
                        ->pluck('country_code', 'country_code')
                        ->toArray()),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Upravit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
