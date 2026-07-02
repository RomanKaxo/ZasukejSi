<?php

namespace App\Filament\Resources\Blogs\Schemas;

use App\Filament\Blocks\Faq;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use SkyRaptor\FilamentBlocksBuilder\Blocks;
use SkyRaptor\FilamentBlocksBuilder\Forms\Components\BlocksInput;

class BlogForm
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

                SpatieMediaLibraryFileUpload::make('header_image')
                    ->label(__('pages.form.header_image'))
                    ->collection('header-image')
                    ->image()
                    ->imageEditor()
                    ->maxSize(5120)
                    ->helperText(__('pages.form.header_image_helper'))
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label(__('pages.form.description') . ' (' . strtoupper(app()->getLocale()) . ')')
                    ->rows(3)
                    ->maxLength(500)
                    ->helperText(__('pages.form.description_helper'))
                    ->afterStateHydrated(function (Textarea $component, $state, $record) {
                        if ($record && $record->exists) {
                            $currentLocale = app()->getLocale();
                            $component->state($record->getTranslation('description', $currentLocale));
                        }
                    })
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

                Toggle::make('is_published')
                    ->label(__('pages.form.is_published'))
                    ->helperText(__('pages.form.is_published_helper'))
                    ->default(true),
            ]);
    }
}
