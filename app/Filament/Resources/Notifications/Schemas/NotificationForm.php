<?php

namespace App\Filament\Resources\Notifications\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class NotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Nadpis')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('message')
                    ->label('Zpráva')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),

                Select::make('type')
                    ->label('Typ')
                    ->options([
                        'info' => 'Informace',
                        'success' => 'Úspěch',
                        'warning' => 'Upozornění',
                        'danger' => 'Chyba/Riziko',
                    ])
                    ->default('info')
                    ->required(),

                Toggle::make('is_global')
                    ->label('Globální (pro všechny uživatele)')
                    ->live()
                    ->default(false),

                Select::make('user_id')
                    ->label('Uživatel')
                    ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(fn (Get $get) => ! $get('is_global'))
                    ->visible(fn (Get $get) => ! $get('is_global'))
                    ->columnSpanFull(),
            ]);
    }
}
