<?php

namespace App\Filament\Resources\ProfileViews;

use App\Filament\Resources\ProfileViews\Pages\ListProfileViews;
use App\Filament\Resources\ProfileViews\Tables\ProfileViewsTable;
use App\Models\ProfileView;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProfileViewResource extends Resource
{
    protected static ?string $model = ProfileView::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 50;

    public static function getNavigationGroup(): ?string
    {
        return 'Statistiky';
    }

    public static function getNavigationLabel(): string
    {
        return 'Zobrazení profilů';
    }

    public static function getModelLabel(): string
    {
        return 'zobrazení';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Zobrazení profilů';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return ProfileViewsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProfileViews::route('/'),
        ];
    }
}
