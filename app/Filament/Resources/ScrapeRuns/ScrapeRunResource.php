<?php

namespace App\Filament\Resources\ScrapeRuns;

use App\Filament\Resources\ScrapeRuns\Pages\ListScrapeRuns;
use App\Filament\Resources\ScrapeRuns\Tables\ScrapeRunsTable;
use App\Models\ScrapeRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Audit trail: when a source was read, how much came back and what failed.
 * Read-only — runs are created by the scraper, never by hand.
 */
class ScrapeRunResource extends Resource
{
    protected static ?string $model = ScrapeRun::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?int $navigationSort = 52;

    public static function getNavigationGroup(): ?string
    {
        return 'Scraper';
    }

    public static function getNavigationLabel(): string
    {
        return 'Historie běhů';
    }

    public static function getModelLabel(): string
    {
        return 'běh';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Běhy';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return ScrapeRunsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScrapeRuns::route('/'),
        ];
    }
}
