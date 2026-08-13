<?php

namespace App\Filament\Resources\Reports\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReportsTable
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

                TextColumn::make('reporter.email')
                    ->label('Nahlásil')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Důvod')
                    ->limit(60)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('allegations')
                    ->label('Kategorie')
                    ->badge()
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state),

                IconColumn::make('blocked_at')
                    ->label('Profil zablokován')
                    ->boolean()
                    ->getStateUsing(fn ($record) => filled($record->blocked_at)),

                TextColumn::make('created_at')
                    ->label('Nahlášeno')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('blocked_at')
                    ->label('Stav')
                    ->nullable()
                    ->placeholder('Vše')
                    ->trueLabel('Zablokováno')
                    ->falseLabel('Nevyřešeno')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('blocked_at'),
                        false: fn ($query) => $query->whereNull('blocked_at'),
                    ),
            ])
            ->recordActions([
                Action::make('block')
                    ->label('Zablokovat profil')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => blank($record->blocked_at) && $record->profile)
                    ->action(function ($record) {
                        $record->profile?->update(['is_public' => false]);
                        $record->update(['blocked_at' => now()]);

                        Notification::make()
                            ->title('Profil byl zablokován')
                            ->success()
                            ->send();
                    }),

                Action::make('unblock')
                    ->label('Odblokovat profil')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => filled($record->blocked_at))
                    ->action(function ($record) {
                        $record->profile?->update(['is_public' => true]);
                        $record->update(['blocked_at' => null]);

                        Notification::make()
                            ->title('Profil byl odblokován')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->label('Smazat nahlášení'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
