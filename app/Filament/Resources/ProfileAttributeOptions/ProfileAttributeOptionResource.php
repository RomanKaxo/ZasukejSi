<?php

namespace App\Filament\Resources\ProfileAttributeOptions;

use App\Filament\Resources\ProfileAttributeOptions\Pages\CreateProfileAttributeOption;
use App\Filament\Resources\ProfileAttributeOptions\Pages\EditProfileAttributeOption;
use App\Filament\Resources\ProfileAttributeOptions\Pages\ListProfileAttributeOptions;
use App\Filament\Resources\ProfileAttributeOptions\Schemas\ProfileAttributeOptionForm;
use App\Filament\Resources\ProfileAttributeOptions\Tables\ProfileAttributeOptionsTable;
use App\Models\ProfileAttributeOption;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * The option lists behind the profile's detail fields.
 *
 * Hair colour, eye colour, bust type and the rest used to be hardcoded — bust
 * size lived as a PHP array inside a Livewire component, and the others did
 * not exist at all, which is why the scraper kept dropping them. They are one
 * table now: the profile form reads it, the scraper checks against it, and
 * anything a source offers beyond it lands in "K doplnění".
 */
class ProfileAttributeOptionResource extends Resource
{
    protected static ?string $model = ProfileAttributeOption::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?int $navigationSort = 21;

    public static function getNavigationLabel(): string
    {
        return 'Vlastnosti profilů';
    }

    public static function getModelLabel(): string
    {
        return 'hodnota vlastnosti';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Vlastnosti profilů';
    }

    public static function form(Schema $schema): Schema
    {
        return ProfileAttributeOptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfileAttributeOptionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProfileAttributeOptions::route('/'),
            'create' => CreateProfileAttributeOption::route('/create'),
            'edit' => EditProfileAttributeOption::route('/{record}/edit'),
        ];
    }
}
