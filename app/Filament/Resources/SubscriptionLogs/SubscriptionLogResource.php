<?php

namespace App\Filament\Resources\SubscriptionLogs;

use App\Filament\Resources\SubscriptionLogs\Pages\ListSubscriptionLogs;
use App\Filament\Resources\SubscriptionLogs\Tables\SubscriptionLogsTable;
use App\Models\SubscriptionLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SubscriptionLogResource extends Resource
{
    protected static ?string $model = SubscriptionLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('subscriptions.navigation.subscriptions');
    }

    public static function getNavigationLabel(): string
    {
        return 'Log předplatných';
    }

    public static function getModelLabel(): string
    {
        return 'log záznam';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Log předplatných';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return SubscriptionLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionLogs::route('/'),
        ];
    }
}
