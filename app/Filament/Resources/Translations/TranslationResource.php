<?php

namespace App\Filament\Resources\Translations;

use App\Filament\Resources\Translations\Pages\EditTranslation;
use App\Filament\Resources\Translations\Pages\ListTranslations;
use App\Filament\Resources\Translations\Schemas\TranslationForm;
use App\Filament\Resources\Translations\Tables\TranslationsTable;
use App\Models\Translation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Every user-facing string on the site, editable.
 *
 * Values live in `lang/*.php` as defaults; a row here overrides one for a
 * locale (see App\Services\DatabaseTranslationLoader). That makes the hero
 * copy, the advert block, the eco badge, button labels and every other `__()`
 * string reachable from the admin — none of them were before.
 */
class TranslationResource extends Resource
{
    protected static ?string $model = Translation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static ?int $navigationSort = 38;

    protected static ?string $recordTitleAttribute = 'key';

    public static function getNavigationGroup(): ?string
    {
        return 'Nastavení';
    }

    public static function getNavigationLabel(): string
    {
        return __('translations.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('translations.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('translations.plural');
    }

    /**
     * Creating a row by hand would produce a key nothing in the code reads —
     * strings arrive through `translations:import`.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return TranslationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TranslationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTranslations::route('/'),
            'edit' => EditTranslation::route('/{record}/edit'),
        ];
    }
}
