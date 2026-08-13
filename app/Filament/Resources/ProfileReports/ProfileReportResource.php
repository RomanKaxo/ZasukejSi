<?php

namespace App\Filament\Resources\ProfileReports;

use App\Filament\Resources\ProfileReports\Pages\ListProfileReports;
use App\Filament\Resources\ProfileReports\Tables\ProfileReportsTable;
use App\Models\ProfileReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProfileReportResource extends Resource
{
    protected static ?string $model = ProfileReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?int $navigationSort = 31;

    public static function getNavigationGroup(): ?string
    {
        return 'Moderace';
    }

    public static function getNavigationLabel(): string
    {
        return 'Anonymní nahlášení';
    }

    public static function getModelLabel(): string
    {
        return 'anonymní nahlášení';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Anonymní nahlášení';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::count();

        return $count > 0 ? (string) $count : null;
    }

    public static function table(Table $table): Table
    {
        return ProfileReportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProfileReports::route('/'),
        ];
    }
}
