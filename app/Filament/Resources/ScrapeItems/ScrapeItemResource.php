<?php

namespace App\Filament\Resources\ScrapeItems;

use App\Filament\Resources\ScrapeItems\Pages\EditScrapeItem;
use App\Filament\Resources\ScrapeItems\Pages\ListScrapeItems;
use App\Filament\Resources\ScrapeItems\Pages\ViewScrapeItem;
use App\Filament\Resources\ScrapeItems\Schemas\ScrapeItemForm;
use App\Filament\Resources\ScrapeItems\Tables\ScrapeItemsTable;
use App\Models\ScrapeItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * The review queue.
 *
 * Scraped rows stop here. Approving one only marks it approved; importing it
 * creates a Profile that is still unpublished and pending, so nothing reaches
 * the site without two deliberate steps.
 */
class ScrapeItemResource extends Resource
{
    protected static ?string $model = ScrapeItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static ?int $navigationSort = 51;

    public static function getNavigationGroup(): ?string
    {
        return 'Scraper';
    }

    public static function getNavigationLabel(): string
    {
        return 'Ke kontrole';
    }

    public static function getModelLabel(): string
    {
        return 'položka';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Položky';
    }

    /** Shows how much is waiting, so the queue is not silently forgotten. */
    public static function getNavigationBadge(): ?string
    {
        $pending = ScrapeItem::pending()->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ScrapeItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScrapeItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScrapeItems::route('/'),
            'view' => ViewScrapeItem::route('/{record}'),
            'edit' => EditScrapeItem::route('/{record}/edit'),
        ];
    }
}
