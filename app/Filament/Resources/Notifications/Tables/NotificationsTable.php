<?php

namespace App\Filament\Resources\Notifications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class NotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Nadpis')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->typeLabel())
                    ->color(fn ($record) => $record->typeBadgeColor())
                    ->sortable(),

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
