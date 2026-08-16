<?php

namespace App\Filament\Resources\FooterMenuItems;

use App\Filament\Resources\FooterMenuItems\Pages\CreateFooterMenuItem;
use App\Filament\Resources\FooterMenuItems\Pages\EditFooterMenuItem;
use App\Filament\Resources\FooterMenuItems\Pages\ListFooterMenuItems;
use App\Filament\Resources\FooterMenuItems\Schemas\FooterMenuItemForm;
use App\Filament\Resources\FooterMenuItems\Tables\FooterMenuItemsTable;
use App\Models\FooterMenuItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FooterMenuItemResource extends Resource
{
    protected static ?string $model = FooterMenuItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3BottomLeft;

    protected static ?int $navigationSort = 21;

    public static function getNavigationGroup(): ?string
    {
        return 'Obsah';
    }

    public static function getNavigationLabel(): string
    {
        return 'Menu v patičce';
    }

    public static function getModelLabel(): string
    {
        return 'položka patičky';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Menu v patičce';
    }

    public static function form(Schema $schema): Schema
    {
        return FooterMenuItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FooterMenuItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFooterMenuItems::route('/'),
            'create' => CreateFooterMenuItem::route('/create'),
            'edit' => EditFooterMenuItem::route('/{record}/edit'),
        ];
    }
}
