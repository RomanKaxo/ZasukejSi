<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Models\Subscription;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewSubscription extends ViewRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('renew')
                ->label(__('subscriptions.actions.renew'))
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading(__('subscriptions.actions.renew_heading'))
                ->modalDescription(fn () => __('subscriptions.actions.renew_description', [
                    'days' => $this->record->subscriptionType->duration_days,
                    'type' => $this->record->subscriptionType->name,
                ]))
                ->action(function () {
                    $this->record->renew();
                    Notification::make()
                        ->success()
                        ->title(__('subscriptions.notifications.renewed'))
                        ->body(__('subscriptions.notifications.renewed_body', [
                            'date' => $this->record->ends_at->format('M d, Y H:i'),
                        ]))
                        ->send();
                })
                ->visible(fn () => $this->record->status !== Subscription::STATUS_CANCELLED),

            Actions\Action::make('cancel')
                ->label(__('subscriptions.actions.cancel'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading(__('subscriptions.actions.cancel_heading'))
                ->modalDescription(__('subscriptions.actions.cancel_description'))
                ->action(function () {
                    $this->record->cancel();
                    Notification::make()
                        ->success()
                        ->title(__('subscriptions.notifications.cancelled'))
                        ->send();
                })
                ->visible(fn () => $this->record->isActive()),

            Actions\DeleteAction::make(),
        ];
    }
}
