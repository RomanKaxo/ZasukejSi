<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\EditContactMessage;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\ContactMessages\Schemas\ContactMessageForm;
use App\Filament\Resources\ContactMessages\Tables\ContactMessagesTable;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?int $navigationSort = 32;

    public static function getNavigationGroup(): ?string
    {
        return __('contact.admin.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('contact.admin.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('contact.admin.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('contact.admin.plural_model_label');
    }

    /** The badge counts what still needs answering, not everything ever sent. */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->whereNull('read_at')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        // Messages arrive from the public form; an admin has nobody to write to
        // here, so there is nothing to create.
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ContactMessageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactMessagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
            'view' => ViewContactMessage::route('/{record}'),
            'edit' => EditContactMessage::route('/{record}/edit'),
        ];
    }
}
