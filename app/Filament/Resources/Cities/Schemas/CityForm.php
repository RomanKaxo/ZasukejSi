<?php

namespace App\Filament\Resources\Cities\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Název')
                    ->required()
                    ->maxLength(255),

                TextInput::make('name_ascii')
                    ->label('Název (ASCII)')
                    ->required()
                    ->maxLength(255),

                TextInput::make('country_code')
                    ->label('Kód země (ISO2)')
                    ->required()
                    ->maxLength(2),

                TextInput::make('admin_name')
                    ->label('Region/kraj')
                    ->maxLength(255),

                TextInput::make('lat')
                    ->label('Zeměpisná šířka')
                    ->numeric(),

                TextInput::make('lng')
                    ->label('Zeměpisná délka')
                    ->numeric(),

                TextInput::make('population')
                    ->label('Počet obyvatel')
                    ->numeric(),
            ]);
    }
}
