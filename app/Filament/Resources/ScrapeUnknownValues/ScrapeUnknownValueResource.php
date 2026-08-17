<?php

namespace App\Filament\Resources\ScrapeUnknownValues;

use App\Filament\Resources\ScrapeUnknownValues\Pages\ListScrapeUnknownValues;
use App\Filament\Resources\ScrapeUnknownValues\Tables\ScrapeUnknownValuesTable;
use App\Models\ScrapeUnknownValue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Values a source offered that our catalogue does not know.
 *
 * The importer never invents catalogue entries, so these are what it had to
 * drop. Approving one creates the real entry and releases every item that was
 * waiting on it.
 */
class ScrapeUnknownValueResource extends Resource
{
    protected static ?string $model = ScrapeUnknownValue::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlusCircle;

    protected static ?int $navigationSort = 52;

    public static function getNavigationGroup(): ?string
    {
        return 'Scraper';
    }

    public static function getNavigationLabel(): string
    {
        return 'K doplnění';
    }

    public static function getModelLabel(): string
    {
        return 'neznámá hodnota';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Hodnoty k doplnění';
    }

    /** What is waiting; the resolved ones are history. */
    public static function getNavigationBadge(): ?string
    {
        $count = ScrapeUnknownValue::query()->pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        // These arrive from harvests; adding a service by hand belongs in the
        // Služby resource.
        return false;
    }

    public static function table(Table $table): Table
    {
        return ScrapeUnknownValuesTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => ListScrapeUnknownValues::route('/')];
    }
}
