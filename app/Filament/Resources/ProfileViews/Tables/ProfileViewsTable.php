<?php

namespace App\Filament\Resources\ProfileViews\Tables;

use App\Models\ProfileView;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProfileViewsTable
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

                TextColumn::make('viewer.email')
                    ->label('Kdo zobrazil')
                    ->default('anonymní')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === ProfileView::TYPE_CLICK ? 'Klik' : 'Zobrazení')
                    ->sortable(),

                TextColumn::make('ip_address')
                    ->label('IP adresa')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('viewed_date')
                    ->label('Datum')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('viewed_date', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Typ')
                    ->options([
                        ProfileView::TYPE_CLICK => 'Klik',
                        ProfileView::TYPE_IMPRESSION => 'Zobrazení',
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
