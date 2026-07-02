<?php

namespace App\Filament\Resources\Profiles\RelationManagers;

use App\Models\Subscription;
use App\Models\SubscriptionType;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('subscriptions.navigation.subscriptions');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
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

                DateTimePicker::make('starts_at')
                    ->label(__('subscriptions.form.starts_at'))
                    ->required()
                    ->default(now())
                    ->seconds(false)
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                        $typeId = $get('subscription_type_id');
                        if ($typeId && $state) {
                            $type = SubscriptionType::find($typeId);
                            if ($type) {
                                $startsAt = Carbon::parse($state);
                                $endsAt = $startsAt->copy()->addDays($type->duration_days);
                                $set('ends_at', $endsAt->format('Y-m-d H:i:s'));
                            }
                        }
                    }),

                DateTimePicker::make('ends_at')
                    ->label(__('subscriptions.form.ends_at'))
                    ->required()
                    ->after('starts_at')
                    ->seconds(false)
                    ->default(function (Get $get) {
                        $typeId = $get('subscription_type_id');
                        if ($typeId) {
                            $type = SubscriptionType::find($typeId);
                            if ($type) {
                                return now()->addDays($type->duration_days);
                            }
                        }
                        return now();
                    }),

                Select::make('status')
                    ->label(__('subscriptions.form.status'))
                    ->options(Subscription::statuses())
                    ->default(Subscription::STATUS_ACTIVE)
                    ->required(),

                Toggle::make('auto_renew')
                    ->label(__('subscriptions.form.auto_renew'))
                    ->default(false),

                Textarea::make('notes')
                    ->label(__('subscriptions.form.notes'))
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('subscriptionType.name')
                    ->label(__('subscriptions.table.type'))
                    ->badge()
                    ->color(fn (Subscription $record) => $record->subscriptionType?->color ?? 'gray'),

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
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(Subscription::statuses()),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (isset($data['starts_at']) && !$data['starts_at'] instanceof \DateTime) {
                            $data['starts_at'] = Carbon::parse($data['starts_at']);
                        }
                        if (isset($data['ends_at']) && !$data['ends_at'] instanceof \DateTime) {
                            $data['ends_at'] = Carbon::parse($data['ends_at']);
                        }
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('renew')
                    ->label(__('subscriptions.actions.renew'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Subscription $record) {
                        $record->renew();
                        Notification::make()
                            ->success()
                            ->title(__('subscriptions.notifications.renewed'))
                            ->send();
                    })
                    ->visible(fn (Subscription $record) => $record->status !== Subscription::STATUS_CANCELLED),

                Action::make('cancel')
                    ->label(__('subscriptions.actions.cancel'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Subscription $record) {
                        $record->cancel();
                        Notification::make()
                            ->success()
                            ->title(__('subscriptions.notifications.cancelled'))
                            ->send();
                    })
                    ->visible(fn (Subscription $record) => $record->isActive()),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
