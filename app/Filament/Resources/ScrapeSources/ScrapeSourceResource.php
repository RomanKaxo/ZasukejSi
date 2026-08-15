<?php

namespace App\Filament\Resources\ScrapeSources;

use App\Filament\Resources\ScrapeSources\Pages\CreateScrapeSource;
use App\Filament\Resources\ScrapeSources\Pages\EditScrapeSource;
use App\Filament\Resources\ScrapeSources\Pages\ListScrapeSources;
use App\Filament\Resources\ScrapeSources\RelationManagers\FieldMapsRelationManager;
use App\Filament\Resources\ScrapeSources\Schemas\ScrapeSourceForm;
use App\Filament\Resources\ScrapeSources\Tables\ScrapeSourcesTable;
use App\Models\ScrapeSource;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * A site the scraper reads, with its selectors.
 *
 * Everything that differs between sites is here rather than in code, so adding
 * a site is a row plus a handful of selectors. Sources start disabled; running
 * one is a deliberate act.
 */
class ScrapeSourceResource extends Resource
{
    protected static ?string $model = ScrapeSource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 50;

    public static function getNavigationGroup(): ?string
    {
        return 'Scraper';
    }

    public static function getNavigationLabel(): string
    {
        return 'Zdroje';
    }

    public static function getModelLabel(): string
    {
        return 'zdroj';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Zdroje';
    }

    public static function form(Schema $schema): Schema
    {
        return ScrapeSourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScrapeSourcesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            FieldMapsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScrapeSources::route('/'),
            'create' => CreateScrapeSource::route('/create'),
            'edit' => EditScrapeSource::route('/{record}/edit'),
        ];
    }
}
