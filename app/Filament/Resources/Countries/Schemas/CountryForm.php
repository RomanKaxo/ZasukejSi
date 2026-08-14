<?php

namespace App\Filament\Resources\Countries\Schemas;

use App\Models\Country;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('code')
                    ->label(__('countries.form.code'))
                    ->required()
                    ->length(2)
                    ->alpha()
                    ->unique(ignoreRecord: true)
                    // Codes join against profiles.country_code and
                    // cities.country_code, both uppercase.
                    ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                    ->dehydrateStateUsing(fn (?string $state) => strtoupper((string) $state))
                    ->live(onBlur: true)
                    // Echo the resolved ISO name back so a typo is obvious
                    // before saving.
                    ->helperText(fn (?string $state) => filled($state) && strlen($state) === 2
                        ? __('countries.form.code_helper') . ' — ' . Country::isoName($state)
                        : __('countries.form.code_helper')),

                TextInput::make('sort_order')
                    ->label(__('countries.form.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->helperText(__('countries.form.sort_order_helper')),

                TextInput::make('name_override')
                    ->label(__('countries.form.name_override') . ' (' . strtoupper(app()->getLocale()) . ')')
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->helperText(__('countries.form.name_override_helper'))
                    ->afterStateHydrated(function (TextInput $component, $state, $record) {
                        if ($record && $record->exists) {
                            $component->state($record->getTranslation('name_override', app()->getLocale(), false));
                        }
                    }),

                Toggle::make('is_visible')
                    ->label(__('countries.form.visible'))
                    ->default(true)
                    ->columnSpanFull()
                    ->helperText(__('countries.form.visible_helper')),
            ]);
    }
}
