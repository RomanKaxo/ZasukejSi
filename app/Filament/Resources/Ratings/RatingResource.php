<?php

namespace App\Filament\Resources\Ratings;

use App\Filament\Resources\Ratings\Pages\ListRatings;
use App\Filament\Resources\Ratings\Tables\RatingsTable;
use App\Models\Rating;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RatingResource extends Resource
{
    protected static ?string $model = Rating::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?int $navigationSort = 32;

    public static function getNavigationGroup(): ?string
    {
        return 'Moderace';
    }

    public static function getNavigationLabel(): string
    {
        return 'Hodnocení';
    }

    public static function getModelLabel(): string
    {
        return 'hodnocení';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Hodnocení';
    }

    public static function table(Table $table): Table
    {
        return RatingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRatings::route('/'),
        ];
    }
}
