<?php

namespace App\Filament\Resources\Ratings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RatingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('profile.display_name')
                    ->label('Profil')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->profile
                        ? route('filament.admin.resources.profiles.view', $record->profile)
                        : null),

                TextColumn::make('user.email')
                    ->label('Hodnotil')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rating')
                    ->label('Hodnocení')
                    ->badge()
                    ->formatStateUsing(fn ($state) => str_repeat('★', (int) $state))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('rating')
                    ->label('Počet hvězd')
                    ->options([
                        1 => '1',
                        2 => '2',
                        3 => '3',
                        4 => '4',
                        5 => '5',
                    ]),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Smazat'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
