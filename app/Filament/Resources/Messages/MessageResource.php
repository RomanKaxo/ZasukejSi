<?php

namespace App\Filament\Resources\Messages;

use App\Filament\Resources\Messages\Pages\ListMessages;
use App\Filament\Resources\Messages\Tables\MessagesTable;
use App\Models\Message;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?int $navigationSort = 33;

    public static function getNavigationGroup(): ?string
    {
        return 'Moderace';
    }

    public static function getNavigationLabel(): string
    {
        return 'Soukromé zprávy';
    }

    public static function getModelLabel(): string
    {
        return 'zpráva';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Soukromé zprávy';
    }

    public static function table(Table $table): Table
    {
        return MessagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessages::route('/'),
        ];
    }
}
