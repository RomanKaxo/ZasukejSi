<?php

namespace App\Filament\Resources\Cities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class CitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // `profiles.city` is free text, not a foreign key, so the count is
            // a correlated subquery on the name — one query for the page
            // rather than one per row.
            ->modifyQueryUsing(fn ($query) => $query->addSelect([
                'profiles_count' => DB::table('profiles')
                    ->selectRaw('count(*)')
                    ->whereColumn('profiles.city', 'cities.name')
                    ->whereNull('profiles.deleted_at'),
            ]))
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

                // Which towns the site actually serves was not answerable
                // here; a list of every seeded city says nothing on its own.
                TextColumn::make('profiles_count')
                    ->label('Profilů')
                    ->badge()
                    ->color(fn ($state) => (int) $state > 0 ? 'success' : 'gray')
                    ->alignCenter()
                    ->sortable(),

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
