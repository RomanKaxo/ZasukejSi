<?php

namespace App\Filament\Widgets;

use App\Models\MemberSubscription;
use App\Models\Profile;
use App\Models\ProfileReport;
use App\Models\ProfileView;
use App\Models\Report;
use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * The admin dashboard shipped with only Filament's own AccountWidget and
 * FilamentInfoWidget — nothing about the site itself. These are the numbers an
 * operator has to act on: what is waiting for them, and what is about to lapse.
 *
 * Every figure links to the resource that resolves it.
 */
class OperationsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            $this->pendingProfiles(),
            $this->openReports(),
            $this->expiringSubscriptions(),
            $this->trafficLast30Days(),
        ];
    }

    private function pendingProfiles(): Stat
    {
        $count = Profile::where('status', 'pending')->count();

        return Stat::make(__('filament.dashboard.pending_profiles'), $count)
            ->description(__('filament.dashboard.pending_profiles_hint'))
            ->color($count > 0 ? 'warning' : 'success')
            ->url(route('filament.admin.resources.profiles.index'));
    }

    private function openReports(): Stat
    {
        // Two separate report streams: Report (from a logged-in member) and
        // ProfileReport (anonymous). Both land in the Moderace group.
        $open = Report::whereNull('blocked_at')->count()
            + ProfileReport::query()->count();

        return Stat::make(__('filament.dashboard.open_reports'), $open)
            ->description(__('filament.dashboard.open_reports_hint'))
            ->color($open > 0 ? 'danger' : 'success')
            ->url(route('filament.admin.resources.reports.index'));
    }

    private function expiringSubscriptions(): Stat
    {
        // Both products at once: provider VIP tiers and member Premium
        // memberships. An operator cares that *something* is about to lapse.
        $profiles = Subscription::query()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereBetween('ends_at', [now(), now()->addDays(7)])
            ->count();

        $members = MemberSubscription::query()->expiring(7)->count();
        $count = $profiles + $members;

        return Stat::make(__('filament.dashboard.expiring_subscriptions'), $count)
            ->description(__('filament.dashboard.expiring_subscriptions_split', [
                'profiles' => $profiles,
                'members' => $members,
            ]))
            ->color($count > 0 ? 'warning' : 'gray')
            ->url(route('filament.admin.resources.subscriptions.index'));
    }

    private function trafficLast30Days(): Stat
    {
        $since = now()->subDays(30)->toDateString();

        $clicks = ProfileView::where('type', ProfileView::TYPE_CLICK)
            ->where('viewed_date', '>=', $since)
            ->count();

        $impressions = ProfileView::where('type', ProfileView::TYPE_IMPRESSION)
            ->where('viewed_date', '>=', $since)
            ->count();

        return Stat::make(__('filament.dashboard.traffic_30_days'), number_format($clicks, 0, ',', ' '))
            ->description(__('filament.dashboard.traffic_30_days_hint', [
                'impressions' => number_format($impressions, 0, ',', ' '),
            ]))
            ->color('info')
            ->url(route('filament.admin.resources.profile-views.index'));
    }
}
