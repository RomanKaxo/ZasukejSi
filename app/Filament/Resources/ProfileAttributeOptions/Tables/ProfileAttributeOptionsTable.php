<?php

namespace App\Filament\Resources\ProfileAttributeOptions\Tables;

use App\Models\ProfileAttributeOption;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class ProfileAttributeOptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Název')
                    ->searchable(query: fn ($query, string $search) => $query->where('normalized', 'like', '%' . ProfileAttributeOptionsTable::normalize($search) . '%'))
                    ->weight('bold'),

                TextColumn::make('attribute')
                    ->label('Vlastnost')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => ProfileAttributeOption::ATTRIBUTES[$state] ?? $state)
                    ->sortable(),

                // How many profiles would lose the value if it were removed —
                // the number that decides whether deactivating is safe.
                TextColumn::make('usage')
                    ->label('Použito u profilů')
                    ->badge()
                    ->color(fn ($state) => (int) $state > 0 ? 'success' : 'gray')
                    ->alignCenter()
                    ->state(fn (ProfileAttributeOption $record) => $record->profileCount()),

                TextColumn::make('sort_order')
                    ->label('Pořadí')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Nabízet')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            // One list at a time: all the eye colours together, then all the
            // hair colours, the way somebody actually edits them.
            ->groups([
                Group::make('attribute')
                    ->label('Vlastnost')
                    ->getTitleFromRecordUsing(
                        fn (ProfileAttributeOption $record) => ProfileAttributeOption::ATTRIBUTES[$record->attribute] ?? $record->attribute
                    ),
            ])
            ->defaultGroup('attribute')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('attribute')
                    ->label('Vlastnost')
                    ->options(ProfileAttributeOption::ATTRIBUTES),

                TernaryFilter::make('is_active')
                    ->label('Nabízené'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Žádné hodnoty')
            ->emptyStateDescription('Tady se spravují nabídky u vlastností profilu — barva očí, barva a délka vlasů, typ a velikost prsou, ochlupení a cestování.');
    }

    /** Search ignores diacritics, the same way the scraper's matching does. */
    private static function normalize(string $value): string
    {
        return \App\Models\ScrapeUnknownValue::normalize($value);
    }
}
