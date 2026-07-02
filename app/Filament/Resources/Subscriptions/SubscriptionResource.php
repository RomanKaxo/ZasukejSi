<?php

namespace App\Filament\Resources\Subscriptions;

use App\Filament\Resources\Subscriptions\Pages\CreateSubscription;
use App\Filament\Resources\Subscriptions\Pages\EditSubscription;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Resources\Subscriptions\Pages\ViewSubscription;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('subscriptions.navigation.subscriptions');
    }

    public static function getNavigationLabel(): string
    {
        return __('subscriptions.navigation.subscriptions');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getModelLabel(): string
    {
        return __('subscriptions.subscription.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('subscriptions.subscription.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make(__('subscriptions.form.subscription_details'))
                    ->schema([
                        Select::make('profile_id')
                            ->label(__('subscriptions.form.profile'))
                            ->relationship('profile', 'display_name')
                            ->getOptionLabelFromRecordUsing(fn (Profile $record) => "{$record->display_name} (ID: {$record->id})")
                            ->searchable(['display_name', 'id'])
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        Select::make('subscription_type_id')
                            ->label(__('subscriptions.form.subscription_type'))
                            ->relationship('subscriptionType', 'name', fn (Builder $query) => $query->active()->ordered())
                            ->getOptionLabelFromRecordUsing(fn (SubscriptionType $record) => "{$record->name} - {$record->formatted_price}/{$record->duration_label}")
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                if ($state) {
                                    $type = SubscriptionType::find($state);
                                    if ($type) {
                                        $startsAt = $get('starts_at') ? Carbon::parse($get('starts_at')) : now();
                                        $set('ends_at', $startsAt->copy()->addDays($type->duration_days)->format('Y-m-d H:i:s'));
                                    }
                                }
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make(__('subscriptions.form.dates'))
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label(__('subscriptions.form.starts_at'))
                            ->required()
                            ->default(now())
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                $typeId = $get('subscription_type_id');
                                if ($typeId && $state) {
                                    $type = SubscriptionType::find($typeId);
                                    if ($type) {
                                        $set('ends_at', Carbon::parse($state)->addDays($type->duration_days)->format('Y-m-d H:i:s'));
                                    }
                                }
                            }),

                        DateTimePicker::make('ends_at')
                            ->label(__('subscriptions.form.ends_at'))
                            ->required()
                            ->after('starts_at'),

                        Select::make('status')
                            ->label(__('subscriptions.form.status'))
                            ->options(Subscription::statuses())
                            ->default(Subscription::STATUS_ACTIVE)
                            ->required(),

                        Toggle::make('auto_renew')
                            ->label(__('subscriptions.form.auto_renew'))
                            ->helperText(__('subscriptions.form.auto_renew_helper'))
                            ->default(false),
                    ])
                    ->columns(2),

                Section::make(__('subscriptions.form.notes'))
                    ->schema([
                        Textarea::make('notes')
                            ->label(__('subscriptions.form.notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('profile.display_name')
                    ->label(__('subscriptions.table.profile'))
                    ->searchable()
                    ->sortable()
                    ->description(fn (Subscription $record) => "ID: {$record->profile_id}"),

                TextColumn::make('subscriptionType.name')
                    ->label(__('subscriptions.table.type'))
                    ->badge()
                    ->color(fn (Subscription $record) => $record->subscriptionType?->color ?? 'gray'),

                TextColumn::make('starts_at')
                    ->label(__('subscriptions.table.starts'))
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ends_at')
                    ->label(__('subscriptions.table.ends'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->description(fn (Subscription $record) => $record->isActive()
                        ? trans_choice('subscriptions.table.days_remaining', $record->days_remaining, ['count' => $record->days_remaining])
                        : null
                    )
                    ->color(fn (Subscription $record) => match (true) {
                        $record->isExpired() => 'danger',
                        $record->is_expiring => 'warning',
                        default => null,
                    }),

                TextColumn::make('status')
                    ->label(__('subscriptions.table.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Subscription::statuses()[$state] ?? $state)
                    ->color(fn (Subscription $record) => $record->status_color),

                IconColumn::make('auto_renew')
                    ->label(__('subscriptions.table.auto_renew'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('subscriptions.filter.status'))
                    ->options(Subscription::statuses()),

                SelectFilter::make('subscription_type_id')
                    ->label(__('subscriptions.filter.type'))
                    ->relationship('subscriptionType', 'name'),

                TernaryFilter::make('expiring_soon')
                    ->label(__('subscriptions.filter.expiring_soon'))
                    ->queries(
                        true: fn (Builder $query) => $query->expiring(7),
                        false: fn (Builder $query) => $query->where(function ($q) {
                            $q->where('ends_at', '>', now()->addDays(7))
                              ->orWhere('status', '!=', 'active');
                        }),
                    ),

                TernaryFilter::make('auto_renew')
                    ->label(__('subscriptions.filter.auto_renew')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('renew')
                    ->label(__('subscriptions.actions.renew'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('subscriptions.actions.renew_heading'))
                    ->modalDescription(fn (Subscription $record) => __('subscriptions.actions.renew_description', [
                        'days' => $record->subscriptionType->duration_days,
                        'type' => $record->subscriptionType->name,
                    ]))
                    ->action(function (Subscription $record) {
                        $record->renew();
                        Notification::make()
                            ->success()
                            ->title(__('subscriptions.notifications.renewed'))
                            ->body(__('subscriptions.notifications.renewed_body', [
                                'date' => $record->ends_at->format('M d, Y H:i'),
                            ]))
                            ->send();
                    })
                    ->visible(fn (Subscription $record) => $record->status !== Subscription::STATUS_CANCELLED),

                Action::make('cancel')
                    ->label(__('subscriptions.actions.cancel'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('subscriptions.actions.cancel_heading'))
                    ->modalDescription(__('subscriptions.actions.cancel_description'))
                    ->form([
                        Textarea::make('reason')
                            ->label(__('subscriptions.form.cancel_reason'))
                            ->rows(3),
                    ])
                    ->action(function (Subscription $record, array $data) {
                        $record->cancel($data['reason'] ?? null);
                        Notification::make()
                            ->success()
                            ->title(__('subscriptions.notifications.cancelled'))
                            ->send();
                    })
                    ->visible(fn (Subscription $record) => $record->isActive()),

                Action::make('reactivate')
                    ->label(__('subscriptions.actions.reactivate'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Subscription $record) {
                        $record->update(['status' => Subscription::STATUS_ACTIVE]);
                        $record->logAction('reactivated');
                        Notification::make()
                            ->success()
                            ->title(__('subscriptions.notifications.reactivated'))
                            ->send();
                    })
                    ->visible(fn (Subscription $record) => $record->isCancelled() && $record->ends_at->isFuture()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_renew')
                        ->label(__('subscriptions.actions.bulk_renew'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->status !== Subscription::STATUS_CANCELLED) {
                                    $record->renew();
                                    $count++;
                                }
                            }
                            Notification::make()
                                ->success()
                                ->title(__('subscriptions.notifications.bulk_renewed', ['count' => $count]))
                                ->send();
                        }),

                    BulkAction::make('bulk_cancel')
                        ->label(__('subscriptions.actions.bulk_cancel'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->isActive()) {
                                    $record->cancel();
                                    $count++;
                                }
                            }
                            Notification::make()
                                ->success()
                                ->title(__('subscriptions.notifications.bulk_cancelled', ['count' => $count]))
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('subscriptions.view.subscription_info'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('profile.display_name')
                                    ->label(__('subscriptions.form.profile'))
                                    ->url(fn (Subscription $record): string => \App\Filament\Resources\Profiles\ProfileResource::getUrl('edit', ['record' => $record->profile_id]))
                                    ->color('primary'),

                                TextEntry::make('subscriptionType.name')
                                    ->label(__('subscriptions.form.subscription_type'))
                                    ->badge(),

                                TextEntry::make('status')
                                    ->label(__('subscriptions.form.status'))
                                    ->badge()
                                    ->formatStateUsing(fn (string $state) => Subscription::statuses()[$state] ?? $state)
                                    ->color(fn (Subscription $record) => $record->status_color),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('starts_at')
                                    ->label(__('subscriptions.form.starts_at'))
                                    ->dateTime('M d, Y H:i'),

                                TextEntry::make('ends_at')
                                    ->label(__('subscriptions.form.ends_at'))
                                    ->dateTime('M d, Y H:i'),

                                TextEntry::make('days_remaining')
                                    ->label(__('subscriptions.view.days_remaining'))
                                    ->badge()
                                    ->color(fn (Subscription $record) => match (true) {
                                        $record->days_remaining <= 0 => 'danger',
                                        $record->days_remaining <= 7 => 'warning',
                                        default => 'success',
                                    }),
                            ]),

                        TextEntry::make('notes')
                            ->label(__('subscriptions.form.notes'))
                            ->columnSpanFull()
                            ->visible(fn (Subscription $record) => filled($record->notes)),
                    ]),

                Section::make(__('subscriptions.view.activity_log'))
                    ->schema([
                        RepeatableEntry::make('logs')
                            ->schema([
                                TextEntry::make('action')
                                    ->label(__('subscriptions.log.action'))
                                    ->badge()
                                    ->formatStateUsing(fn (string $state) => \App\Models\SubscriptionLog::actions()[$state] ?? $state)
                                    ->color(fn ($record) => $record->action_color),

                                TextEntry::make('user.name')
                                    ->label(__('subscriptions.log.by'))
                                    ->default(__('subscriptions.log.system')),

                                TextEntry::make('created_at')
                                    ->label(__('subscriptions.log.date'))
                                    ->dateTime('M d, Y H:i'),

                                TextEntry::make('metadata')
                                    ->label(__('subscriptions.log.details'))
                                    ->formatStateUsing(fn ($state) => is_array($state) ? collect($state)->map(fn ($v, $k) => "$k: $v")->join(', ') : '')
                                    ->visible(fn ($record) => filled($record->metadata)),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptions::route('/'),
            'create' => CreateSubscription::route('/create'),
            'view' => ViewSubscription::route('/{record}'),
            'edit' => EditSubscription::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\Subscriptions\Widgets\SubscriptionStatsWidget::class,
        ];
    }
}
