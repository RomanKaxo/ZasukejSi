<?php

namespace App\Filament\Resources\Translations\Schemas;

use App\Support\Locales;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TranslationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('locale')
                    ->label(__('translations.form.locale'))
                    ->options(collect(Locales::all())->map(fn ($m) => $m['native'])->all())
                    ->required()
                    ->native(false),

                TextInput::make('group')
                    ->label(__('translations.form.group'))
                    ->required()
                    ->maxLength(64)
                    ->helperText(__('translations.form.group_helper')),

                TextInput::make('key')
                    ->label(__('translations.form.key'))
                    ->required()
                    ->maxLength(191)
                    ->columnSpanFull()
                    ->helperText(__('translations.form.key_helper')),

                Textarea::make('value')
                    ->label(__('translations.form.value'))
                    ->rows(4)
                    ->columnSpanFull()
                    ->helperText(__('translations.form.value_helper')),

                // Read-only: the file is what defines the default, so editing it
                // here would be a lie the next import overwrites.
                Textarea::make('default_value')
                    ->label(__('translations.form.default'))
                    ->rows(3)
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
            ]);
    }
}
