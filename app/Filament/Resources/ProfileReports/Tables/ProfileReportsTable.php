<?php

namespace App\Filament\Resources\ProfileReports\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ProfileReportsTable
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

                TextColumn::make('email')
                    ->label('E-mail oznamovatele')
                    ->searchable(),

                TextColumn::make('message')
                    ->label('Zpráva')
                    ->wrap()
                    ->limit(200)
                    ->searchable(),

                ImageColumn::make('screenshot_path')
                    ->label('Screenshot')
                    ->getStateUsing(fn ($record) => $record->screenshot_path
                        ? Storage::disk('public')->url($record->screenshot_path)
                        : null)
                    ->square()
                    ->url(fn ($record) => $record->screenshot_path
                        ? Storage::disk('public')->url($record->screenshot_path)
                        : null)
                    ->openUrlInNewTab(),

                TextColumn::make('created_at')
                    ->label('Nahlášeno')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('block')
                    ->label('Zablokovat profil')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->profile && $record->profile->is_public)
                    ->action(function ($record) {
                        $record->profile?->update(['is_public' => false]);

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
                    ->visible(fn ($record) => $record->profile && ! $record->profile->is_public)
                    ->action(function ($record) {
                        $record->profile?->update(['is_public' => true]);

                        Notification::make()
                            ->title('Profil byl odblokován')
                            ->success()
                            ->send();
                    }),

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
