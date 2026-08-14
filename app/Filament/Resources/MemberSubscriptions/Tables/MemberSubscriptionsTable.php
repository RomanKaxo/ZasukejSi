<?php

namespace App\Filament\Resources\MemberSubscriptions\Tables;

use App\Models\MemberSubscription;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MemberSubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('subscriptions.member_subscription.user'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('subscriptionType.name')
                    ->label(__('subscriptions.type.singular'))
                    ->badge(),

                TextColumn::make('status')
                    ->label(__('subscriptions.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => MemberSubscription::statuses()[$state] ?? $state)
                    ->color(fn ($record) => $record->status_color),

                TextColumn::make('starts_at')
                    ->label(__('subscriptions.fields.starts_at'))
                    ->dateTime('d.m.Y')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label(__('subscriptions.fields.ends_at'))
                    ->dateTime('d.m.Y')
                    ->sortable()
                    // Highlight what is about to lapse — the operator's cue to act.
                    ->color(fn ($record) => $record->is_expiring ? 'warning' : null)
                    ->description(fn ($record) => $record->isActive()
                        ? trans_choice('subscriptions.table.days_remaining', $record->days_remaining, ['count' => $record->days_remaining])
                        : null),

                IconColumn::make('auto_renew')
                    ->label(__('subscriptions.fields.auto_renew'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('common.Created'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('ends_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('subscriptions.fields.status'))
                    ->options(MemberSubscription::statuses()),

                SelectFilter::make('subscription_type_id')
                    ->label(__('subscriptions.type.singular'))
                    ->relationship('subscriptionType', 'slug'),

                Filter::make('expiring')
                    ->label(__('subscriptions.filter.expiring_soon'))
                    ->query(fn ($query) => $query->expiring(7)),
            ])
            ->recordActions([
                EditAction::make(),

                // Same verbs the MemberSubscription model exposes, so the admin
                // and the checkout flow stay in step.
                Action::make('renew')
                    ->label(__('subscriptions.actions.renew'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => ! $record->isCancelled())
                    ->action(fn ($record) => $record->renew()),

                Action::make('cancel')
                    ->label(__('subscriptions.actions.cancel'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->isActive())
                    ->action(fn ($record) => $record->cancel()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
