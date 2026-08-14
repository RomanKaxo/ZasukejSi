<?php

namespace App\Filament\Resources\Countries\Tables;

use App\Services\CountryStatsService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CountriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('countries.table.code'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('display_name')
                    ->label(__('countries.table.name'))
                    ->getStateUsing(fn ($record) => $record->display_name),

                // Read-only: counts are always derived from the profiles table,
                // never stored, so the admin can never set a number the public
                // listing cannot back up.
                TextColumn::make('profiles_count')
                    ->label(__('countries.table.profiles_count'))
                    ->tooltip(__('countries.table.profiles_count_tooltip'))
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return app(CountryStatsService::class)
                            ->countries()
                            ->firstWhere('code', $record->code)
                            ?->profiles_count ?? 0;
                    }),

                TextColumn::make('sort_order')
                    ->label(__('countries.table.sort_order'))
                    ->sortable(),

                IconColumn::make('is_visible')
                    ->label(__('countries.table.visible'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('countries.table.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
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
