<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('filament.attributes.name'))
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label(__('filament.attributes.email'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('phone')
                    ->label(__('filament.attributes.phone'))
                    ->tel()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    // `phone` is nullable+unique in the DB; MySQL treats
                    // repeated empty strings as duplicates (unlike NULL), so
                    // clearing this field for a second user would otherwise
                    // collide and fail to save.
                    ->dehydrateStateUsing(fn (?string $state) => filled($state) ? $state : null),

                Select::make('gender')
                    ->label(__('filament.attributes.gender'))
                    ->options([
                        'male' => __('filament.attributes.gender_male'),
                        'female' => __('filament.attributes.gender_female'),
                    ])
                    ->required()
                    ->native(false),

                TextInput::make('password')
                    ->label(__('filament.attributes.password'))
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->same('passwordConfirmation')
                    ->dehydrated(fn ($state): bool => filled($state)),

                TextInput::make('passwordConfirmation')
                    ->label(__('filament.attributes.password_confirmation'))
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->dehydrated(false),

                Select::make('roles')
                    ->label(__('filament.attributes.roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
