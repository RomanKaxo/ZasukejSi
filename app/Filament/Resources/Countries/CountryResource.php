<?php

namespace App\Filament\Resources\Countries;

use App\Filament\Resources\Countries\Pages\CreateCountry;
use App\Filament\Resources\Countries\Pages\EditCountry;
use App\Filament\Resources\Countries\Pages\ListCountries;
use App\Filament\Resources\Countries\Schemas\CountryForm;
use App\Filament\Resources\Countries\Tables\CountriesTable;
use App\Models\Country;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Controls which countries appear in the public country lists and in what
 * order. Names default to lang/{cs,en}/codes.php, regions come from
 * `cities.admin_name`, and profile counts are always derived from `profiles` —
 * none of those are editable here, so the admin cannot publish a number the
 * listing cannot back up.
 *
 * Sits next to Cities in the "Nastavení" group, since the two together define
 * the site's geography.
 */
class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeEuropeAfrica;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?int $navigationSort = 39;

    public static function getNavigationGroup(): ?string
    {
        return 'Nastavení';
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.navigation.countries');
    }

    public static function getModelLabel(): string
    {
        return __('common.Country');
    }

    public static function getPluralModelLabel(): string
    {
        return __('common.Countries');
    }

    public static function form(Schema $schema): Schema
    {
        return CountryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CountriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCountries::route('/'),
            'create' => CreateCountry::route('/create'),
            'edit' => EditCountry::route('/{record}/edit'),
        ];
    }
}
