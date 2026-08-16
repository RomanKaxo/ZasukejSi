<?php

namespace App\Filament\Resources\Messages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fromUser.email')
                    ->label('Od')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('toUser.email')
                    ->label('Komu')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('message')
                    ->label('Zpráva')
                    ->limit(80)
                    ->wrap()
                    // Anything past 80 characters was simply cut off with no
                    // way to read the rest anywhere in the admin.
                    ->tooltip(fn ($record) => mb_strlen((string) $record->message) > 80 ? $record->message : null)
                    ->searchable(),

                IconColumn::make('read_at')
                    ->label('Přečteno')
                    ->boolean()
                    ->getStateUsing(fn ($record) => filled($record->read_at)),

                TextColumn::make('created_at')
                    ->label('Odesláno')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Smazat'),
                RestoreAction::make()
                    ->label('Obnovit'),
                ForceDeleteAction::make()
                    ->label('Smazat trvale'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
