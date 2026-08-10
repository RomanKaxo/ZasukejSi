<?php

namespace App\Filament\Resources\Segments\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SegmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label(__('segments.form.name') . ' (' . strtoupper(app()->getLocale()) . ')')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->afterStateHydrated(function (TextInput $component, $state, $record) {
                        if ($record && $record->exists) {
                            $currentLocale = app()->getLocale();
                            $component->state($record->getTranslation('name', $currentLocale));
                        }
                    }),

                TextInput::make('slug')
                    ->label(__('segments.form.slug'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                ColorPicker::make('color')
                    ->label(__('segments.form.color'))
                    ->default('#5C2D62'),

                TextInput::make('icon')
                    ->label(__('segments.form.icon'))
                    ->maxLength(100),

                TextInput::make('sort_order')
                    ->label(__('segments.form.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->helperText(__('segments.form.sort_order_helper')),

                Toggle::make('is_active')
                    ->label(__('segments.form.active'))
                    ->default(true)
                    ->helperText(__('segments.form.active_helper')),
            ]);
    }
}
