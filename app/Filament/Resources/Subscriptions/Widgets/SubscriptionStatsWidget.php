<?php

namespace App\Filament\Resources\Subscriptions\Widgets;

use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SubscriptionStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $activeCount = Subscription::active()->count();
        $expiringCount = Subscription::expiring(7)->count();
        $expiredThisMonth = Subscription::where('status', Subscription::STATUS_EXPIRED)
            ->whereMonth('updated_at', now()->month)
            ->count();
        $newThisMonth = Subscription::whereMonth('created_at', now()->month)->count();

        return [
            Stat::make(__('subscriptions.stats.active'), $activeCount)
                ->description(__('subscriptions.stats.active_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('subscriptions.stats.expiring_soon'), $expiringCount)
                ->description(__('subscriptions.stats.expiring_description'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($expiringCount > 0 ? 'warning' : 'gray'),

            Stat::make(__('subscriptions.stats.new_this_month'), $newThisMonth)
                ->description(__('subscriptions.stats.new_description'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),

            Stat::make(__('subscriptions.stats.expired_this_month'), $expiredThisMonth)
                ->description(__('subscriptions.stats.expired_description'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
        ];
    }
}
