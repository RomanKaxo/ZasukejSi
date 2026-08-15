<?php

namespace App\Filament\Resources\Profiles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use SkyRaptor\FilamentBlocksBuilder\Blocks;
use SkyRaptor\FilamentBlocksBuilder\Forms\Components\BlocksInput;
use App\Filament\Blocks\Faq;

class ProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isAdmin = $user?->hasRole('admin') ?? false;
        $record = $schema->getRecord();
        $profileUrl = ($record && $record->exists && $record->id) 
                    ? route('profiles.show', ['id' => $record->id]) 
                    : null;

        return $schema
            ->columns(2)
            ->components([
            // The subscription lived only in a relation tab, so the one thing
            // an admin usually opens a profile to check needed a click to see.
            \Filament\Forms\Components\Placeholder::make('subscription_summary')
                ->label(__('profiles.form.subscription_state'))
                ->visible(fn () => $record && $record->exists)
                ->columnSpanFull()
                ->content(function () use ($record): string {
                    if (! $record) {
                        return '—';
                    }

                    $subscription = $record->activeSubscription()->with('subscriptionType')->first();

                    if (! $subscription) {
                        return __('profiles.form.subscription_none');
                    }

                    $name = $subscription->subscriptionType?->getTranslation('name', app()->getLocale())
                        ?? $subscription->subscriptionType?->slug
                        ?? '—';

                    return __('profiles.form.subscription_active', [
                        'plan' => $name,
                        'date' => optional($subscription->ends_at)->format('d.m.Y') ?? '—',
                    ]);
                }),

            Select::make('user_id')
                ->relationship('user', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->visible($isAdmin)
                ->columnSpanFull(),

            // Display profile URL if record exists
            // `profile_url` is derived, not a column, so there is no state to
            // hydrate from the record. `default()` only fires on create, which
            // left the field blank on every edit — the one screen where the
            // address is actually wanted.
            TextInput::make('profile_url')
                ->label(__('profiles.form.profile_url'))
                ->formatStateUsing(fn () => $profileUrl)
                ->dehydrated(false)
                ->disabled()
                ->visible(fn () => $profileUrl !== null)
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

                Toggle::make('is_porn_actress')
                    ->label(__('profiles.form.is_porn_actress'))
                    ->default(false)
                    ->inline(false),

                // Physical/descriptive attributes live in the `content` JSON map
                // and were previously only writable from the provider-facing
                // Livewire form — an admin could not correct them. The frontend
                // renders an empty placeholder wherever one of these is blank
                // (it never invents a value), so making them editable here is
                // what actually fills the profile cards and detail pages.
                //
                // EditProfile/CreateProfile merge this map over the stored one,
                // so keys not present in this form are preserved.
                TextInput::make('content.card_height_cm')
                    ->label(__('profiles.form.card_height_cm'))
                    ->numeric()
                    ->minValue(120)
                    ->maxValue(230)
                    ->suffix('cm')
                    ->helperText(__('profiles.form.card_height_cm_helper')),

                TextInput::make('content.weight_kg')
                    ->label(__('profiles.form.weight_kg'))
                    ->numeric()
                    ->minValue(30)
                    ->maxValue(300)
                    ->suffix('kg'),

                Select::make('content.bust_size')
                    ->label(__('profiles.form.bust_size'))
                    ->options(array_combine(
                        ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'],
                        ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']
                    ))
                    ->native(false),

                TextInput::make('content.nationality')
                    ->label(__('profiles.form.nationality'))
                    ->maxLength(2)
                    ->helperText(__('profiles.form.nationality_helper')),

                TextInput::make('content.languages')
                    ->label(__('profiles.form.languages'))
                    ->maxLength(255)
                    ->helperText(__('profiles.form.languages_helper'))
                    ->columnSpanFull(),

                TextInput::make('content.card_location')
                    ->label(__('profiles.form.card_location'))
                    ->maxLength(255)
                    ->helperText(__('profiles.form.card_location_helper'))
                    ->columnSpanFull(),

                Toggle::make('content.is_showcase')
                    ->label(__('profiles.form.is_showcase'))
                    ->inline(false)
                    ->visible($isAdmin)
                    ->helperText(__('profiles.form.is_showcase_helper')),

                // Bound to `content_blocks`, not `content`. Both used to share
                // one column, which made every profile with physical attributes
                // throw on open ("Argument #1 ($itemData) must be of type array,
                // int given") and would have wiped those attributes on save.
                BlocksInput::make('content_blocks')
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

                // Two writers, two shapes: the member-facing ProfileForm saves
                // this as a list (explode on ", "), the admin as a day => hours
                // map. Whichever wrote last decided the shape, and a nested
                // value reached the KeyValue as an array — which is what
                // rendered as "[object Object]".
                KeyValue::make('availability_hours')
                    ->label(__('profiles.form.availability_hours'))
                    ->keyLabel(__('profiles.form.day_label'))
                    ->valueLabel(__('profiles.form.hours_label'))
                    ->formatStateUsing(function ($state): array {
                        if (! is_array($state) || $state === []) {
                            return [];
                        }

                        // Recursive: a value can be nested more than one level
                        // deep (a day holding slots holding from/to pairs), and
                        // strval() on an inner array raises "Array to string
                        // conversion" rather than returning anything.
                        $flatten = function ($value) use (&$flatten): string {
                            if (is_array($value)) {
                                return implode('-', array_filter(array_map($flatten, $value), fn ($p) => $p !== ''));
                            }

                            if (is_bool($value)) {
                                return $value ? '1' : '0';
                            }

                            return $value === null ? '' : (string) $value;
                        };

                        $normalized = [];

                        foreach ($state as $key => $value) {
                            // A list entry carries the day inside the text
                            // ("Pondělí 9:00-17:00"); split it back apart.
                            if (is_int($key) && is_string($value)) {
                                if (preg_match('/^\s*(\p{L}+)\s+(.*)$/u', $value, $m)) {
                                    $normalized[$m[1]] = trim($m[2]);
                                } else {
                                    $normalized[trim($value)] = '';
                                }

                                continue;
                            }

                            // A nested value (from/to pairs, or slots holding
                            // pairs) flattened to text.
                            $normalized[(string) $key] = $flatten($value);
                        }

                        return $normalized;
                    })
                    ->columnSpanFull()
                    ->helperText(__('profiles.form.availability_helper')),

                // The price fields hardcoded a "$" prefix, so a Czech provider
                // entering korunas had them labelled as dollars. The amounts
                // now carry the currency they were entered in, and the prefix
                // follows it.
                Select::make('price_currency')
                    ->label(__('profiles.form.price_currency'))
                    ->options(\App\Support\Currencies::all())
                    ->default(\App\Support\Currencies::CZK)
                    ->required()
                    ->live()
                    ->helperText(__('profiles.form.price_currency_helper')),

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
                            ->prefix(fn ($get) => \App\Support\Currencies::symbol(
                                (string) ($get('../../price_currency') ?: \App\Support\Currencies::CZK)
                            )),
                        TextInput::make('outcall_price')
                            ->label(__('profiles.form.outcall_price'))
                            ->required()
                            ->numeric()
                            ->prefix(fn ($get) => \App\Support\Currencies::symbol(
                                (string) ($get('../../price_currency') ?: \App\Support\Currencies::CZK)
                            )),
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
                            ->prefix(fn ($get) => \App\Support\Currencies::symbol(
                                (string) ($get('../../price_currency') ?: \App\Support\Currencies::CZK)
                            )),
                        TextInput::make('outcall_price')
                            ->label(__('profiles.form.outcall_price'))
                            ->required()
                            ->numeric()
                            ->prefix(fn ($get) => \App\Support\Currencies::symbol(
                                (string) ($get('../../price_currency') ?: \App\Support\Currencies::CZK)
                            )),
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

                CheckboxList::make('segments')
                    ->label(__('common.Segments'))
                    ->relationship('segments', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', app()->getLocale()))
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible($isAdmin),
            ]);
    }
}
