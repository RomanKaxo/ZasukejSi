<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use App\Models\ContactMessage;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * What an admin sees when opening a contact message.
 *
 * The sender's own fields are read-only — this is a record of what was sent,
 * not something to rewrite. Only the status and the internal note are the
 * admin's to change.
 */
class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make(__('contact.admin.sender'))
                    ->columns(2)
                    ->columnSpanFull()
                    ->components([
                        TextInput::make('first_name')
                            ->label(__('contact.form.first_name'))
                            ->disabled(),

                        TextInput::make('last_name')
                            ->label(__('contact.form.last_name'))
                            ->disabled(),

                        TextInput::make('email')
                            ->label(__('contact.admin.email'))
                            ->disabled(),

                        TextInput::make('phone')
                            ->label(__('contact.admin.phone'))
                            ->disabled(),

                        Placeholder::make('account')
                            ->label(__('contact.admin.account'))
                            ->content(fn (?ContactMessage $record) => $record?->user
                                ? $record->user->name . ' (' . $record->user->email . ')'
                                : __('contact.admin.guest')),

                        Placeholder::make('linked_profile')
                            ->label(__('contact.admin.profile'))
                            ->content(fn (?ContactMessage $record) => $record?->profile?->display_name ?? '—'),
                    ]),

                Section::make(__('contact.admin.message'))
                    ->columnSpanFull()
                    ->components([
                        Textarea::make('message')
                            ->label(__('contact.admin.message'))
                            ->rows(10)
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Section::make(__('contact.admin.context_section'))
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsed()
                    ->components([
                        Placeholder::make('received_at')
                            ->label(__('contact.admin.received_at'))
                            ->content(fn (?ContactMessage $record) => $record?->created_at?->format('d.m.Y H:i') ?? '—'),

                        Placeholder::make('read_state')
                            ->label(__('contact.admin.read_at'))
                            ->content(fn (?ContactMessage $record) => $record?->read_at?->format('d.m.Y H:i')
                                ?? __('contact.admin.unread')),

                        Placeholder::make('locale_value')
                            ->label(__('contact.admin.locale'))
                            ->content(fn (?ContactMessage $record) => $record?->locale ?? '—'),

                        Placeholder::make('ip_value')
                            ->label(__('contact.admin.ip_address'))
                            ->content(fn (?ContactMessage $record) => $record?->ip_address ?? '—'),

                        Placeholder::make('user_agent_value')
                            ->label(__('contact.admin.user_agent'))
                            ->columnSpanFull()
                            ->content(fn (?ContactMessage $record) => $record?->user_agent ?? '—'),
                    ]),

                Section::make(__('contact.admin.status'))
                    ->columns(2)
                    ->columnSpanFull()
                    ->components([
                        Select::make('status')
                            ->label(__('contact.admin.status'))
                            ->options(fn () => ContactMessage::statusOptions())
                            ->default(ContactMessage::STATUS_NEW)
                            ->required(),

                        Textarea::make('admin_note')
                            ->label(__('contact.admin.admin_note'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
