<?php

namespace App\Filament\Resources\ProfileViews;

use App\Filament\Resources\ProfileViews\Pages\ListProfileViews;
use App\Filament\Resources\ProfileViews\Tables\ProfileViewsTable;
use App\Models\Profile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProfileViewResource extends Resource
{
    // Model je Profil, ne jednotlivé zobrazení: sekce odpovídá na otázku
    // „kdo je nejvíc vidět", ne „kdo kdy klikl".
    protected static ?string $model = Profile::class;

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
        return 'profil';
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
