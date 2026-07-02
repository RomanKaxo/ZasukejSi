<?php

namespace App\Filament\Resources\Profiles\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use FilamentTiptapEditor\TiptapEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use SkyRaptor\FilamentBlocksBuilder\Blocks;
use SkyRaptor\FilamentBlocksBuilder\Forms\Components\BlocksInput;
use App\Filament\Blocks\Faq;

class ProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isAdmin = $user && ($user->email === 'test@example.com'); // Temporary admin check
        $record = $schema->getRecord();
        $profileUrl = ($record && $record->exists && $record->id) 
                    ? route('profiles.show', ['id' => $record->id]) 
                    : null;

        return $schema
            ->columns(2)
            ->components([
            Select::make('user_id')
                ->relationship('user', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->visible($isAdmin)
                ->columnSpanFull(),

            // Display profile URL if record exists
            TextInput::make('profile_url')
                ->label(__('profiles.form.profile_url'))
                ->default($profileUrl)
                ->disabled()
                ->visible(fn() => $profileUrl !== null)
                ->columnSpanFull()
                ->helperText(__('profiles.form.profile_url_helper')),


                SpatieMediaLibraryFileUpload::make('images')
                    ->label(__('profiles.form.profile_images'))
                    ->collection('profile-images')
                    ->multiple()
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png'])
                    ->maxFiles(10)
                    ->imageEditor()
                    ->columnSpanFull(),

                SpatieMediaLibraryFileUpload::make('video')
                    ->label(__('profiles.form.profile_video'))
                    ->collection('profile-video')
                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime'])
                    ->maxSize(153600) // 150MB max
                    ->columnSpanFull()
                    ->helperText(__('profiles.form.profile_video_helper')),

                // Current locale translatable fields
                TextInput::make('display_name')
                    ->label(__('profiles.form.display_name') . ' (' . strtoupper(app()->getLocale()) . ')')
                    ->required()
                    ->maxLength(255)
                    ->afterStateHydrated(function (TextInput $component, $state, $record) {
                        if ($record && $record->exists) {
                            $currentLocale = app()->getLocale();
                            $component->state($record->getTranslation('display_name', $currentLocale));
                        }
                    }),

                MarkdownEditor::make('about')
                    ->label(__('profiles.form.about') . ' (' . strtoupper(app()->getLocale()) . ')')
                    ->maxLength(2000)
                    ->columnSpanFull()
                    ->afterStateHydrated(function (MarkdownEditor $component, $state, $record) {
                        if ($record && $record->exists) {
                            $currentLocale = app()->getLocale();
                            $component->state($record->getTranslation('about', $currentLocale));
                        }
                    }),

                Toggle::make('incall')
                    ->label(__('profiles.form.incall'))
                    ->default(false)
                    ->inline(false),

                Toggle::make('outcall')
                    ->label(__('profiles.form.outcall'))
                    ->default(false)
                    ->inline(false),

                BlocksInput::make('content')
                    ->label(__('profiles.form.profile_content_builder'))
                    ->blocks(fn() => [
                        Blocks\Card::block($schema),
                        Blocks\Typography\Heading::block($schema),
                        Blocks\Typography\Paragraph::block($schema),
                        Faq::block($schema),
                    ])
                    ->columnSpanFull()
                    ->helperText(__('profiles.form.profile_content_helper')),

                TextInput::make('age')
                    ->label(__('profiles.form.age'))
                    ->numeric()
                    ->minValue(18)
                    ->maxValue(99),

                TextInput::make('city')
                    ->label(__('profiles.form.city'))
                    ->maxLength(255),

                Select::make('country_code')
                    ->label(__('profiles.form.country'))
                    ->options(function () {
                        $codes = include base_path('lang/en/codes.php');
                        return collect($codes)->mapWithKeys(fn($name, $code) => [strtolower($code) => $name]);
                    })
                    ->searchable(),

                TextInput::make('address')
                    ->label(__('profiles.form.address'))
                    ->maxLength(1200)
                    ->columnSpanFull(),

                KeyValue::make('availability_hours')
                    ->label(__('profiles.form.availability_hours'))
                    ->keyLabel(__('profiles.form.day_label'))
                    ->valueLabel(__('profiles.form.hours_label'))
                    ->columnSpanFull()
                    ->helperText(__('profiles.form.availability_helper')),

                Repeater::make('local_prices')
                    ->label(__('profiles.form.local_prices'))
                    ->schema([
                        TextInput::make('time_hours')
                            ->label(__('profiles.form.time_hours'))
                            ->required()
                            ->maxLength(100),
                        TextInput::make('incall_price')
                            ->label(__('profiles.form.incall_price'))
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('outcall_price')
                            ->label(__('profiles.form.outcall_price'))
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->collapsible()
                    ->defaultItems(0)
                    ->addActionLabel(__('profiles.form.add_price')),

                Repeater::make('global_prices')
                    ->label(__('profiles.form.global_prices'))
                    ->schema([
                        TextInput::make('time_hours')
                            ->label(__('profiles.form.time_hours'))
                            ->required()
                            ->maxLength(100),
                        TextInput::make('incall_price')
                            ->label(__('profiles.form.incall_price'))
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('outcall_price')
                            ->label(__('profiles.form.outcall_price'))
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->collapsible()
                    ->defaultItems(0)
                    ->addActionLabel(__('profiles.form.add_price')),

                Repeater::make('contacts')
                    ->label(__('profiles.form.contacts'))
                    ->schema([
                        Select::make('type')
                            ->label(__('profiles.form.contact_type'))
                            ->options([
                                'phone' => __('profiles.form.contact_phone'),
                                'whatsapp' => 'WhatsApp',
                                'telegram' => 'Telegram',
                            ])
                            ->required(),
                        TextInput::make('value')
                            ->label(__('profiles.form.contact_value'))
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible()
                    ->defaultItems(0)
                    ->addActionLabel(__('profiles.form.add_contact')),

                Select::make('status')
                    ->label(__('profiles.form.status'))
                    ->options([
                        'draft' => __('profiles.status.draft'),
                        'pending' => __('profiles.status.pending'),
                        'approved' => __('profiles.status.approved'),
                        'rejected' => __('profiles.status.rejected'),
                    ])
                    ->default('draft')
                    ->visible($isAdmin),

                DateTimePicker::make('verified_at')
                    ->label(__('profiles.form.verified_at'))
                    ->visible($isAdmin),

                Toggle::make('is_public')
                    ->label(__('profiles.form.is_public'))
                    ->default(true),
            ]);
    }
}
