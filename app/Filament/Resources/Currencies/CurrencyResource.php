<?php

namespace App\Filament\Resources\Currencies;

use App\Filament\Resources\Currencies\Pages\CreateCurrency;
use App\Filament\Resources\Currencies\Pages\EditCurrency;
use App\Filament\Resources\Currencies\Pages\ListCurrencies;
use App\Models\Currency;
use App\Support\Locales;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Currencies the site quotes prices in.
 *
 * The rate is only ever used to offer a provider the amounts she has not
 * filled in herself. A price she typed is never recalculated — a rate drifts,
 * and that would quietly change what a customer is charged.
 */
class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?int $navigationSort = 41;

    public static function getNavigationGroup(): ?string
    {
        return 'Nastavení';
    }

    public static function getNavigationLabel(): string
    {
        return 'Měny';
    }

    public static function getModelLabel(): string
    {
        return 'měna';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Měny';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Měna')
                ->schema([
                    TextInput::make('code')
                        ->label('Kód (ISO 4217)')
                        ->required()
                        ->maxLength(3)
                        ->unique(ignoreRecord: true)
                        ->helperText('Například CZK, EUR, USD.'),

                    TextInput::make('symbol')
                        ->label('Symbol')
                        ->required()
                        ->maxLength(8),

                    ...array_map(
                        fn (string $locale) => TextInput::make("name.{$locale}")
                            ->label('Název (' . Locales::nativeName($locale) . ')')
                            ->required($locale === 'cs'),
                        Locales::codes(),
                    ),
                ])
                ->columns(2),

            Section::make('Kurz a dostupnost')
                ->description('Kurz se používá jen k nabídnutí přepočtu. Cena, kterou poskytovatelka zadala, se nikdy nepřepočítává.')
                ->schema([
                    TextInput::make('exchange_rate')
                        ->label('Kurz vůči základní měně')
                        ->numeric()
                        ->required()
                        ->minValue(0.000001)
                        ->step(0.000001)
                        ->helperText('Kolik jednotek této měny koupí jedna jednotka základní měny.')
                        ->disabled(fn ($get) => (bool) $get('is_base'))
                        ->dehydrated(),

                    TextInput::make('sort_order')
                        ->label('Pořadí')
                        ->numeric()
                        ->default(0),

                    Toggle::make('is_base')
                        ->label('Základní měna')
                        ->live()
                        ->helperText('Jen jedna měna může být základní; její kurz je vždy 1.'),

                    Toggle::make('is_active')
                        ->label('Zapnutá')
                        ->default(true)
                        ->helperText('Vypnutá měna se nikde nenabízí, ale zadané ceny zůstanou.'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kód')->badge()->sortable(),
                TextColumn::make('symbol')->label('Symbol'),
                TextColumn::make('name')
                    ->label('Název')
                    ->formatStateUsing(fn ($record) => $record->getTranslation('name', app()->getLocale())),
                TextColumn::make('exchange_rate')
                    ->label('Kurz')
                    ->formatStateUsing(fn ($state, $record) => $record->is_base ? '— (základní)' : rtrim(rtrim((string) $state, '0'), '.')),
                IconColumn::make('is_base')->label('Základní')->boolean(),
                IconColumn::make('is_active')->label('Zapnutá')->boolean(),
                TextColumn::make('sort_order')->label('Pořadí')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make()->label('Upravit'),
                DeleteAction::make()
                    ->label('Smazat')
                    // Deleting the yardstick would leave every rate meaningless.
                    ->visible(fn (Currency $record) => ! $record->is_base),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCurrencies::route('/'),
            'create' => CreateCurrency::route('/create'),
            'edit' => EditCurrency::route('/{record}/edit'),
        ];
    }
}
