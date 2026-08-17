<?php

namespace App\Filament\Resources\Ratings\Tables;

use App\Support\RatingScale;
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
                    ->sortable()
                    // A rating is only worth judging next to the account that
                    // left it; that account was a string of text.
                    ->url(fn ($record) => $record->user
                        ? route('filament.admin.resources.users.edit', $record->user)
                        : null),

                // The percentage is what the member chose and what averages
                // are computed from; the stars below are its projection.
                TextColumn::make('percentage')
                    ->label('Hodnocení')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state . ' %')
                    ->color(fn ($state) => match (true) {
                        $state >= RatingScale::THRESHOLD_GOOD => 'success',
                        $state >= RatingScale::THRESHOLD_FAIR => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                TextColumn::make('rating')
                    ->label('Hvězdy')
                    ->formatStateUsing(fn ($state, $record) => str_repeat('★', (int) $state)
                        . '  (' . number_format(RatingScale::toStars((float) $record->percentage), 1) . '/5)')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('percentage')
                    ->label('Hodnocení')
                    ->options(fn () => collect(RatingScale::options())
                        ->mapWithKeys(fn (int $percentage) => [$percentage => $percentage . ' %'])
                        ->all()),
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
