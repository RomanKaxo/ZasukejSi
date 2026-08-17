<?php

namespace App\Filament\Resources\Profiles\Schemas;

use App\Models\ProfileAttributeOption;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
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


                // Thumbnails in a grid rather than one per row: a profile
                // carries up to ten photos and the stacked list made the form
                // scroll for a screen and a half.
                SpatieMediaLibraryFileUpload::make('images')
                    ->label(__('profiles.form.profile_images'))
                    ->collection('profile-images')
                    ->multiple()
                    ->reorderable()
                    ->appendFiles()
                    ->panelLayout('grid')
                    ->imagePreviewHeight('150')
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                    ->maxFiles(10)
                    ->imageEditor()
                    ->extraAttributes(['class' => 'profile-media-grid'])
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
                // Its own column and its own field. It used to exist only as a
                // row in the contact list below, so there was nowhere to type
                // a number an admin was holding — and nowhere to search one.
                // Saving keeps the contact list in step.
                TextInput::make('phone')
                    ->label(__('profiles.form.phone'))
                    ->tel()
                    ->maxLength(32)
                    ->helperText(__('profiles.form.phone_helper')),

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

                // These six lists live in Vlastnosti profilů. Bust size used to
                // be a hardcoded array in two places, and the other five did
                // not exist at all — which is why the scraper kept fetching an
                // eye colour and then dropping it.
                Select::make('content.bust_size')
                    ->label(__('profiles.form.bust_size'))
                    ->options(fn () => ProfileAttributeOption::optionsFor('bust_size'))
                    ->native(false),

                Select::make('content.bust_type')
                    ->label('Typ prsou')
                    ->options(fn () => ProfileAttributeOption::optionsFor('bust_type'))
                    ->native(false),

                Select::make('content.eye_colour')
                    ->label('Barva očí')
                    ->options(fn () => ProfileAttributeOption::optionsFor('eye_colour'))
                    ->native(false),

                Select::make('content.hair_colour')
                    ->label('Barva vlasů')
                    ->options(fn () => ProfileAttributeOption::optionsFor('hair_colour'))
                    ->native(false),

                Select::make('content.hair_length')
                    ->label('Délka vlasů')
                    ->options(fn () => ProfileAttributeOption::optionsFor('hair_length'))
                    ->native(false),

                Select::make('content.pubic_hair')
                    ->label('Ochlupení')
                    ->options(fn () => ProfileAttributeOption::optionsFor('pubic_hair'))
                    ->native(false),

                Select::make('content.travels')
                    ->label('Cestování')
                    ->options(fn () => ProfileAttributeOption::optionsFor('travels'))
                    ->native(false)
                    ->helperText('Kam je ochotná vycestovat. Nabídku spravujete ve Vlastnostech profilů.'),

                TextInput::make('content.nationality')
                    ->label(__('profiles.form.nationality'))
                    ->maxLength(2)
                    ->helperText(__('profiles.form.nationality_helper')),

                // Psalo se volným textem, takže „Angličtina", „anglicky" a „EN"
                // byly tři různé hodnoty a nešlo podle nich filtrovat. Ukládá
                // se dál čárkami oddělený řetězec, aby na to nemusel sahat
                // zbytek webu — mění se jen způsob zadání.
                Select::make('content.languages')
                    ->label(__('profiles.form.languages'))
                    ->multiple()
                    ->searchable()
                    ->preload()
                    // K nabídce se přidá i to, co profil má uložené dnes.
                    // Jazyky se dřív psaly ručně, a přísný výběr by takovou
                    // hodnotu při prvním uložení potichu zahodil.
                    ->options(function (callable $get) {
                        $stored = $get('content.languages');
                        $stored = is_array($stored)
                            ? $stored
                            : array_filter(array_map('trim', explode(',', (string) $stored)));

                        return ProfileAttributeOption::optionsFor('languages')
                            + array_combine($stored, $stored);
                    })
                    ->formatStateUsing(fn ($state) => is_array($state)
                        ? $state
                        : array_values(array_filter(array_map('trim', explode(',', (string) $state)))))
                    ->dehydrateStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state)
                    ->helperText('Nabídku spravujete ve Vlastnostech profilů. ' . __('profiles.form.languages_helper'))
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

                // Město se psalo ručně, takže „Praha", „praha" a „Praha 1" byly
                // tři lokality. Vybírá se z tabulky měst, zúžené na zvolenou
                // zemi; hledá se na serveru, protože měst je přes 48 tisíc.
                Select::make('city')
                    ->label(__('profiles.form.city'))
                    ->searchable()
                    ->allowHtml(false)
                    ->getSearchResultsUsing(function (string $search, callable $get) {
                        $country = strtoupper((string) $get('country_code'));

                        return \App\Models\City::query()
                            ->when($country !== '', fn ($q) => $q->where('country_code', $country))
                            ->where(function ($q) use ($search) {
                                $q->where('name', 'like', $search . '%')
                                    ->orWhere('name_ascii', 'like', $search . '%');
                            })
                            ->orderBy('name')
                            ->limit(50)
                            ->pluck('name', 'name')
                            ->all();
                    })
                    // Hodnota uložená dřív ručně nezmizí jen proto, že ji
                    // seznam měst nezná.
                    ->getOptionLabelUsing(fn ($value) => $value)
                    ->helperText('Vyberte ze seznamu měst. Nabídka se řídí zvolenou zemí.'),

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

                // A day is a day: seven fixed rows with a range, not free-form
                // keys. The KeyValue this replaces showed the raw shape —
                // "always_online" and "schedule" as literal keys with the
                // values dumped beside them.
                Toggle::make('availability_hours.always_online')
                    ->label(__('profiles.form.always_online'))
                    ->helperText(__('profiles.form.availability_helper'))
                    ->live()
                    ->columnSpanFull(),

                Fieldset::make(__('profiles.form.availability_hours'))
                    ->schema(
                        collect(\App\Support\Availability::DAYS)
                            ->map(fn (string $day) => Group::make([
                                TextInput::make("availability_hours.schedule.{$day}.from")
                                    ->label(\App\Support\Availability::dayLabel($day))
                                    ->placeholder('9:00')
                                    ->maxLength(5),

                                TextInput::make("availability_hours.schedule.{$day}.to")
                                    ->label(__('profiles.form.hours_to'))
                                    ->placeholder('17:00')
                                    ->maxLength(5),
                            ])->columns(2))
                            ->all()
                    )
                    ->columns(2)
                    ->columnSpanFull()
                    ->hidden(fn ($get) => (bool) $get('availability_hours.always_online')),

                // The price fields hardcoded a "$" prefix, so a Czech provider
                // entering korunas had them labelled as dollars. The amounts
                // now carry the currency they were entered in, and the prefix
                // follows it.
                Select::make('price_currency')
                    ->label(__('profiles.form.price_currency'))
                    ->options(fn () => \App\Models\Currency::options())
                    ->default(fn () => \App\Models\Currency::base()?->code ?? \App\Support\Currencies::CZK)
                    ->required()
                    ->live()
                    ->helperText(__('profiles.form.price_currency_helper')),

                Toggle::make('auto_convert_prices')
                    ->label(__('profiles.form.auto_convert'))
                    ->helperText(__('profiles.form.auto_convert_helper')),

                // One amount per active currency, per service. Services had no
                // price at all before, so a provider could say what she offers
                // but not what it costs.
                Repeater::make('servicePrices')
                    ->label(__('profiles.form.service_prices'))
                    ->schema([
                        Select::make('service_id')
                            ->label(__('profiles.form.service'))
                            ->options(fn () => \App\Models\Service::query()
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->get()
                                ->mapWithKeys(fn ($s) => [$s->id => $s->getTranslation('name', app()->getLocale())])
                                ->all())
                            ->searchable()
                            ->required()
                            ->distinct()
                            ->columnSpan(2),

                        ...collect(\App\Models\Currency::active())
                            ->map(fn ($currency) => TextInput::make('prices.' . $currency->code)
                                ->label($currency->code)
                                ->numeric()
                                ->minValue(0)
                                ->prefix($currency->symbol))
                            ->all(),

                        TextInput::make('note')
                            ->label(__('profiles.form.service_note'))
                            ->maxLength(120)
                            ->columnSpanFull(),
                    ])
                    ->columns(2 + max(1, \App\Models\Currency::active()->count()))
                    ->columnSpanFull()
                    ->collapsible()
                    ->defaultItems(0)
                    ->addActionLabel(__('profiles.form.add_service_price'))
                    ->helperText(__('profiles.form.service_prices_helper')),

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
