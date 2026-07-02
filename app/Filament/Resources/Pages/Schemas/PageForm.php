<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Blocks\Faq;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use SkyRaptor\FilamentBlocksBuilder\Blocks;
use SkyRaptor\FilamentBlocksBuilder\Forms\Components\BlocksInput;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label(__('pages.form.title') . ' (' . strtoupper(app()->getLocale()) . ')')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state)))
                    ->afterStateHydrated(function (TextInput $component, $state, $record) {
                        if ($record && $record->exists) {
                            $currentLocale = app()->getLocale();
                            $component->state($record->getTranslation('title', $currentLocale));
                        }
                    })
                    ->columnSpanFull(),

                TextInput::make('slug')
                    ->label(__('pages.form.slug'))
                    ->required()
                    ->unique(table: 'pages', ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText(__('pages.form.slug_helper'))
                    ->prefix(url('/') . '/')
                    ->columnSpanFull(),

                BlocksInput::make('content')
                    ->label(__('pages.form.content') . ' (' . strtoupper(app()->getLocale()) . ')')
                    ->blocks(fn() => [
                        Blocks\Typography\Heading::block($schema),
                        Blocks\Typography\Paragraph::block($schema),
                        Blocks\Card::block($schema),
                        Faq::block($schema),
                    ])
                    ->afterStateHydrated(function (BlocksInput $component, $state, $record) {
                        if ($record && $record->exists) {
                            $currentLocale = app()->getLocale();
                            $component->state($record->getTranslation('content', $currentLocale));
                        }
                    })
                    ->columnSpanFull()
                    ->helperText(__('pages.form.content_helper')),

                Toggle::make('display_in_menu')
                    ->label(__('pages.form.display_in_menu'))
                    ->helperText(__('pages.form.display_in_menu_helper'))
                    ->default(false),

                Toggle::make('display_in_footer')
                    ->label(__('pages.form.display_in_footer'))
                    ->helperText(__('pages.form.display_in_footer_helper'))
                    ->default(false),

                Toggle::make('is_published')
                    ->label(__('pages.form.is_published'))
                    ->helperText(__('pages.form.is_published_helper'))
                    ->default(true),
            ]);
    }
}
