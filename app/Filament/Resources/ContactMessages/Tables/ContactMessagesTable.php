<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('contact.admin.received_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('full_name')
                    ->label(__('contact.admin.name'))
                    ->searchable(['first_name', 'last_name'])
                    // An unread message is the one that still needs doing.
                    ->weight(fn (ContactMessage $record) => $record->isRead() ? null : 'bold'),

                TextColumn::make('email')
                    ->label(__('contact.admin.email'))
                    ->searchable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->label(__('contact.admin.phone'))
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('user.name')
                    ->label(__('contact.admin.account'))
                    ->placeholder(__('contact.admin.guest'))
                    ->searchable(),

                TextColumn::make('profile.display_name')
                    ->label(__('contact.admin.profile'))
                    ->placeholder('—')
                    ->url(fn (ContactMessage $record) => $record->profile
                        ? route('filament.admin.resources.profiles.view', $record->profile)
                        : null),

                TextColumn::make('message')
                    ->label(__('contact.admin.message'))
                    ->wrap()
                    ->limit(120)
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label(__('contact.admin.status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => ContactMessage::statusOptions()[$state] ?? $state)
                    // A status is a plain column, so the set can grow without a
                    // deploy; an unknown one must not throw.
                    ->color(fn (?string $state) => match ($state) {
                        ContactMessage::STATUS_NEW => 'warning',
                        ContactMessage::STATUS_IN_PROGRESS => 'info',
                        ContactMessage::STATUS_RESOLVED => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('contact.admin.status'))
                    ->options(fn () => ContactMessage::statusOptions()),

                Filter::make('unread')
                    ->label(__('contact.admin.unread'))
                    ->query(fn ($query) => $query->whereNull('read_at')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('markAsRead')
                    ->label(__('contact.admin.mark_as_read'))
                    ->icon('heroicon-o-envelope-open')
                    ->visible(fn (ContactMessage $record) => ! $record->isRead())
                    ->action(function (ContactMessage $record) {
                        $record->markAsRead();

                        Notification::make()
                            ->title(__('contact.admin.marked_as_read'))
                            ->success()
                            ->send();
                    }),

                // Poznámka šla napsat jen přes editaci celé zprávy, což je
                // formulář o pěti sekcích kvůli jednomu poli. Tohle je to
                // pole samo.
                Action::make('note')
                    ->label(__('contact.admin.admin_note'))
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray')
                    ->modalHeading(__('contact.admin.admin_note'))
                    ->modalDescription(__('contact.admin.admin_note_helper'))
                    ->modalSubmitActionLabel(__('contact.admin.save_note'))
                    ->fillForm(fn (ContactMessage $record) => ['admin_note' => $record->admin_note])
                    ->schema([
                        Textarea::make('admin_note')
                            ->label(__('contact.admin.admin_note'))
                            ->rows(5)
                            ->maxLength(2000),
                    ])
                    ->action(function (ContactMessage $record, array $data) {
                        $record->update(['admin_note' => $data['admin_note'] ?? null]);

                        Notification::make()
                            ->title(__('contact.admin.note_saved'))
                            ->success()
                            ->send();
                    }),

                Action::make('reply')
                    ->label(__('contact.admin.reply'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->url(fn (ContactMessage $record) => 'mailto:' . $record->email)
                    ->openUrlInNewTab(),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
