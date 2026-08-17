<?php

namespace App\Filament\Resources\ProfileAttributeOptions\Schemas;

use App\Models\ProfileAttributeOption;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProfileAttributeOptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('attribute')
                    ->label('Vlastnost')
                    ->options(ProfileAttributeOption::ATTRIBUTES)
                    ->required()
                    ->native(false)
                    ->helperText('Kam hodnota patří — nabídne se u profilu právě pod touto vlastností.'),

                TextInput::make('label')
                    ->label('Název (' . strtoupper(app()->getLocale()) . ')')
                    ->required()
                    ->maxLength(120)
                    ->afterStateHydrated(function (TextInput $component, $state, $record) {
                        if ($record && $record->exists) {
                            $component->state($record->getTranslation('label', app()->getLocale()));
                        }
                    }),

                TextInput::make('sort_order')
                    ->label('Pořadí')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->helperText('Nižší číslo je v nabídce výš.'),

                Toggle::make('is_active')
                    ->label('Nabízet')
                    ->default(true)
                    // Deactivating rather than deleting: profiles that already
                    // have the value keep it, it just stops being offered.
                    ->helperText('Vypnutá hodnota se přestane nabízet, ale profilům, které ji mají, zůstane.'),
            ]);
    }
}
