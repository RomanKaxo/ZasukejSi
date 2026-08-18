<?php

namespace App\Filament\Resources\Notifications\Tables;

use App\Models\Notification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class NotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Nadpis')
                    ->searchable()
                    ->sortable()
                    // The list showed headlines only, so telling two
                    // notifications apart meant opening both.
                    ->description(fn ($record) => Str::limit((string) $record->message, 90))
                    ->tooltip(fn ($record) => $record->message)
                    ->wrap(),

                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->typeLabel())
                    ->color(fn ($record) => $record->typeBadgeColor())
                    ->sortable(),

                // Provozní zprávy scraperu a zprávy pro návštěvníky vypadaly
                // v seznamu stejně, takže „globální" znamenalo dvě různé věci.
                TextColumn::make('audience')
                    ->label('Komu')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === Notification::AUDIENCE_ADMIN
                        ? 'Administrace'
                        : 'Návštěvníkům')
                    ->color(fn ($state) => $state === Notification::AUDIENCE_ADMIN ? 'warning' : 'gray'),

                TextColumn::make('user.email')
                    ->label('Příjemce')
                    ->default('Všichni')
                    ->searchable(),

                IconColumn::make('is_global')
                    ->label('Globální')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('read_at')
                    ->label('Přečteno')
                    ->boolean()
                    ->getStateUsing(fn ($record) => filled($record->read_at)),

                TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('audience')
                    ->label('Komu')
                    ->options([
                        Notification::AUDIENCE_ADMIN => 'Administrace',
                        Notification::AUDIENCE_PUBLIC => 'Návštěvníkům',
                    ]),

                SelectFilter::make('type')
                    ->label('Typ')
                    ->options([
                        'info' => 'Informace',
                        'success' => 'Úspěch',
                        'warning' => 'Upozornění',
                        'danger' => 'Chyba/Riziko',
                    ]),
                TernaryFilter::make('is_global')
                    ->label('Globální'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
