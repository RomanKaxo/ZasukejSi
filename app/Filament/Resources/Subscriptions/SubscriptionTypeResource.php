<?php

namespace App\Filament\Resources\Subscriptions;

use App\Filament\Resources\Subscriptions\Pages\CreateSubscriptionType;
use App\Filament\Resources\Subscriptions\Pages\EditSubscriptionType;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptionTypes;
use App\Models\SubscriptionType;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionTypeResource extends Resource
{
    protected static ?string $model = SubscriptionType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'subscription-types';

    public static function getNavigationGroup(): ?string
    {
        return __('subscriptions.navigation.subscriptions');
    }

    public static function getNavigationLabel(): string
    {
        return __('subscriptions.navigation.types');
    }

    public static function getModelLabel(): string
    {
        return __('subscriptions.type.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('subscriptions.type.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make(__('subscriptions.form.basic_info'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('subscriptions.form.name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label(__('subscriptions.form.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->alphaDash(),

                        // Plans are sold to two different audiences: VIP tiers
                        // for provider profiles, and the Premium membership that
                        // unlocks ratings for members. The checkout flows query
                        // this to avoid offering one audience the other's plan.
                        Select::make('audience')
                            ->label(__('subscriptions.fields.audience'))
                            ->options(SubscriptionType::audiences())
                            ->default(SubscriptionType::AUDIENCE_PROFILE)
                            ->required()
                            ->native(false)
                            ->helperText(__('subscriptions.fields.audience_helper')),

                        Textarea::make('description')
                            ->label(__('subscriptions.form.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('subscriptions.form.pricing'))
                    ->schema([
                        // One field per currency. Prices are not converted at
                        // display time, because a rate that drifts would
                        // quietly misquote what the customer is charged.
                        TextInput::make('price_czk')
                            ->label('Cena (Kč)')
                            ->numeric()
                            ->suffix('Kč')
                            ->minValue(0),

                        TextInput::make('price_eur')
                            ->label('Cena (EUR)')
                            ->numeric()
                            ->prefix('€')
                            ->minValue(0),

                        TextInput::make('price_usd')
                            ->label('Cena (USD)')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0),

                        TextInput::make('price')
                            ->label(__('subscriptions.form.price'))
                            ->helperText('Původní sloupec bez měny. Zůstává kvůli starším záznamům; vyplňujte ceny výše.')
                            ->numeric()
                            ->required()
                            ->default(0),

                        Select::make('duration_days')
                            ->label(__('subscriptions.form.duration'))
                            ->options([
                                7 => __('subscriptions.duration.weekly'),
                                14 => '14 dní',
                                30 => __('subscriptions.duration.monthly'),
                                60 => '60 dní',
                                90 => __('subscriptions.duration.quarterly'),
                                120 => '120 dní',
                                180 => __('subscriptions.duration.semi_annual'),
                                270 => '270 dní',
                                365 => __('subscriptions.duration.yearly'),
                                730 => '2 roky',
                            ])
                            ->required()
                            ->default(30),
                    ])
                    ->columns(2),

                Section::make(__('subscriptions.form.appearance'))
                    ->schema([
                        Select::make('color')
                            ->label(__('subscriptions.form.color'))
                            ->options([
                                'primary' => 'Primary',
                                'success' => 'Success (Green)',
                                'warning' => 'Warning (Yellow)',
                                'danger' => 'Danger (Red)',
                                'info' => 'Info (Blue)',
                                'gray' => 'Gray',
                            ])
                            ->default('primary'),

                        Select::make('icon')
                            ->label(__('subscriptions.form.icon'))
                            ->options([
                                'heroicon-o-star' => '⭐ Star',
                                'heroicon-o-sparkles' => '✨ Sparkles',
                                'heroicon-o-bolt' => '⚡ Bolt',
                                'heroicon-o-fire' => '🔥 Fire',
                                'heroicon-o-crown' => '👑 Crown',
                                'heroicon-o-trophy' => '🏆 Trophy',
                                'heroicon-o-rocket-launch' => '🚀 Rocket',
                                'heroicon-o-heart' => '❤️ Heart',
                            ])
                            ->searchable(),

                        TextInput::make('sort_order')
                            ->label(__('subscriptions.form.sort_order'))
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label(__('subscriptions.form.is_active'))
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make(__('subscriptions.form.features'))
                    ->schema([
                        KeyValue::make('features')
                            ->label(__('subscriptions.form.features_list'))
                            ->keyLabel(__('subscriptions.form.feature_key'))
                            ->valueLabel(__('subscriptions.form.feature_value'))
                            ->addActionLabel(__('subscriptions.form.add_feature'))
                            ->columnSpanFull()
                            ->helperText(__('subscriptions.form.features_helper')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('subscriptions.table.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label(__('subscriptions.table.slug'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // All three currencies at once, so a plan priced in only one of
                // them is visible as such rather than looking complete.
                TextColumn::make('prices')
                    ->label(__('subscriptions.table.price'))
                    ->state(function ($record) {
                        $parts = [];

                        foreach (\App\Support\Currencies::codes() as $code) {
                            $amount = $record->priceIn($code);

                            if ($amount !== null) {
                                $parts[] = \App\Support\Currencies::format($amount, $code);
                            }
                        }

                        return $parts === [] ? '—' : implode(' · ', $parts);
                    })
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('price', $direction)),

                TextColumn::make('duration_label')
                    ->label(__('subscriptions.table.duration')),

                TextColumn::make('active_subscriptions_count')
                    ->label(__('subscriptions.table.active_count'))
                    ->counts('activeSubscriptions')
                    ->badge()
                    ->color('success'),

                IconColumn::make('is_active')
                    ->label(__('subscriptions.table.active'))
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label(__('subscriptions.table.order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('subscriptions.filter.active')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionTypes::route('/'),
            'create' => CreateSubscriptionType::route('/create'),
            'edit' => EditSubscriptionType::route('/{record}/edit'),
        ];
    }
}
