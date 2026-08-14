<?php

namespace App\Console\Commands;

use App\Models\MemberSubscription;
use App\Models\Notification;
use App\Models\Subscription;
use Illuminate\Console\Command;

/**
 * Moves subscriptions through their own lifecycle.
 *
 * Nothing did this before. `Subscription::expire()` existed with no caller, and
 * the `notifications.*.expiring_soon_*` strings were translated in both locales
 * but never sent — so a lapsed subscription sat in the database as `active`
 * with a past `ends_at` forever, the admin table and stats widget counted it as
 * live, and nobody was warned before losing access.
 *
 * Run daily (see routes/console.php).
 */
class ProcessSubscriptionLifecycle extends Command
{
    protected $signature = 'subscriptions:lifecycle
                            {--days=7 : How many days ahead to warn about expiry}
                            {--dry-run : Report what would change without touching anything}';

    protected $description = 'Expire lapsed subscriptions and warn about ones expiring soon';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $dryRun = (bool) $this->option('dry-run');

        $expiredProfiles = $this->expireProfileSubscriptions($dryRun);
        $expiredMembers = $this->expireMemberships($dryRun);
        $warnedProfiles = $this->warnProfileSubscriptions($days, $dryRun);
        $warnedMembers = $this->warnMemberships($days, $dryRun);

        $this->info(sprintf(
            '%sExpired: %d profile, %d membership. Warned: %d profile, %d membership.',
            $dryRun ? '[dry run] ' : '',
            $expiredProfiles,
            $expiredMembers,
            $warnedProfiles,
            $warnedMembers
        ));

        return self::SUCCESS;
    }

    private function expireProfileSubscriptions(bool $dryRun): int
    {
        $count = 0;

        Subscription::query()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where('ends_at', '<=', now())
            ->chunkById(200, function ($subscriptions) use (&$count, $dryRun) {
                foreach ($subscriptions as $subscription) {
                    $count++;

                    if (! $dryRun) {
                        // expire() logs the action and the model's `updating`
                        // hook notifies the profile owner.
                        $subscription->expire();
                    }
                }
            });

        return $count;
    }

    private function expireMemberships(bool $dryRun): int
    {
        $count = 0;

        MemberSubscription::query()
            ->where('status', MemberSubscription::STATUS_ACTIVE)
            ->where('ends_at', '<=', now())
            ->chunkById(200, function ($memberships) use (&$count, $dryRun) {
                foreach ($memberships as $membership) {
                    $count++;

                    if (! $dryRun) {
                        $membership->expire();
                    }
                }
            });

        return $count;
    }

    private function warnProfileSubscriptions(int $days, bool $dryRun): int
    {
        $count = 0;

        Subscription::query()
            ->with('profile')
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereBetween('ends_at', [now(), now()->addDays($days)])
            // Warn once per period, not every night.
            ->whereNull('expiring_notified_at')
            ->chunkById(200, function ($subscriptions) use (&$count, $dryRun) {
                foreach ($subscriptions as $subscription) {
                    if (! $subscription->profile?->user_id) {
                        continue;
                    }

                    $count++;

                    if ($dryRun) {
                        continue;
                    }

                    Notification::createForUser(
                        $subscription->profile->user_id,
                        __('notifications.subscription.expiring_soon_title'),
                        __('notifications.subscription.expiring_soon_message', [
                            'days' => max(0, (int) now()->diffInDays($subscription->ends_at, false)),
                        ]),
                        'warning'
                    );

                    $subscription->forceFill(['expiring_notified_at' => now()])->saveQuietly();
                }
            });

        return $count;
    }

    private function warnMemberships(int $days, bool $dryRun): int
    {
        $count = 0;

        MemberSubscription::query()
            ->where('status', MemberSubscription::STATUS_ACTIVE)
            ->whereBetween('ends_at', [now(), now()->addDays($days)])
            ->whereNull('expiring_notified_at')
            ->chunkById(200, function ($memberships) use (&$count, $dryRun) {
                foreach ($memberships as $membership) {
                    $count++;

                    if ($dryRun) {
                        continue;
                    }

                    Notification::createForUser(
                        $membership->user_id,
                        __('notifications.membership.expiring_soon_title'),
                        __('notifications.membership.expiring_soon_message', [
                            'days' => max(0, (int) now()->diffInDays($membership->ends_at, false)),
                        ]),
                        'warning'
                    );

                    // saveQuietly: this is bookkeeping, not a status change, and
                    // must not trigger the model's notification hooks.
                    $membership->forceFill(['expiring_notified_at' => now()])->saveQuietly();
                }
            });

        return $count;
    }
}
