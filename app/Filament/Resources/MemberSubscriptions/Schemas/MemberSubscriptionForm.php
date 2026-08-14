<?php

namespace App\Filament\Resources\MemberSubscriptions\Schemas;

use App\Models\MemberSubscription;
use App\Models\SubscriptionType;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MemberSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('user_id')
                    ->label(__('subscriptions.member_subscription.user'))
                    ->required()
                    ->searchable()
                    ->preload()
                    // Memberships are the member-side product; a provider buys a
                    // profile VIP tier instead.
                    ->options(fn () => User::query()
                        ->where('gender', 'male')
                        ->orderBy('name')
                        ->pluck('name', 'id')),

                Select::make('subscription_type_id')
                    ->label(__('subscriptions.type.singular'))
                    ->required()
                    ->options(fn () => SubscriptionType::forMembers()
                        ->ordered()
                        ->get()
                        ->mapWithKeys(fn (SubscriptionType $type) => [
                            $type->id => $type->getTranslation('name', app()->getLocale()),
                        ]))
                    ->live()
                    // Pre-fill the period from the chosen plan so the operator
                    // does not have to compute it.
                    ->afterStateUpdated(function ($state, callable $set) {
                        $type = SubscriptionType::find($state);

                        if ($type) {
                            $set('starts_at', now());
                            $set('ends_at', now()->addDays($type->duration_days));
                        }
                    }),

                DateTimePicker::make('starts_at')
                    ->label(__('subscriptions.fields.starts_at'))
                    ->required()
                    ->default(now()),

                DateTimePicker::make('ends_at')
                    ->label(__('subscriptions.fields.ends_at'))
                    ->required()
                    ->after('starts_at'),

                Select::make('status')
                    ->label(__('subscriptions.fields.status'))
                    ->required()
                    ->options(MemberSubscription::statuses())
                    ->default(MemberSubscription::STATUS_ACTIVE),

                Toggle::make('auto_renew')
                    ->label(__('subscriptions.fields.auto_renew'))
                    ->helperText(__('subscriptions.fields.auto_renew_helper'))
                    ->default(false),

                Textarea::make('notes')
                    ->label(__('subscriptions.fields.cancel_reason'))
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
