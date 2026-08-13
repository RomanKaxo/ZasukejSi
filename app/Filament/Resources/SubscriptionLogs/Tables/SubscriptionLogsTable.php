<?php

namespace App\Filament\Resources\SubscriptionLogs\Tables;

use App\Models\SubscriptionLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subscription.profile.display_name')
                    ->label('Profil')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('action')
                    ->label('Akce')
                    ->badge()
                    ->color(fn ($record) => $record->action_color)
                    ->formatStateUsing(fn ($record) => $record->action_label),

                TextColumn::make('user.email')
                    ->label('Provedl')
                    ->searchable()
                    ->default('systém'),

                TextColumn::make('notes')
                    ->label('Poznámka')
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Kdy')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')
                    ->label('Akce')
                    ->options(SubscriptionLog::actions()),
            ]);
    }
}
