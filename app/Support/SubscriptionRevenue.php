<?php

namespace App\Support;

use App\Models\MemberSubscription;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * What the two products actually earned, and how many of them are running.
 *
 * The dashboard could say how many profiles were waiting for approval but not
 * a single thing about money, which is the other half of running this.
 *
 * Two rules keep the figures honest:
 *
 *   · what Stripe charged wins over what the plan costs today, because a plan's
 *     price can be edited after somebody has already paid the old one;
 *   · a subscription granted without payment (no gateway configured — see
 *     {@see OfflineCheckout}) counts towards „active", never towards revenue.
 */
class SubscriptionRevenue
{
    /** Currency the dashboard reports in. */
    public const CURRENCY = Currencies::CZK;

    /**
     * Revenue per month for the last twelve, oldest first.
     *
     * @return array<string, float> „2026-08" => částka
     */
    public function monthly(int $months = 12): array
    {
        $since = now()->subMonths($months - 1)->startOfMonth();

        $buckets = [];
        $cursor = $since->copy();

        while ($cursor->lte(now())) {
            $buckets[$cursor->format('Y-m')] = 0.0;
            $cursor->addMonth();
        }

        foreach ($this->paidRows($since) as $row) {
            $month = $row['at']->format('Y-m');

            if (array_key_exists($month, $buckets)) {
                $buckets[$month] += $row['amount'];
            }
        }

        return $buckets;
    }

    /** Everything earned since a date, or ever when null. */
    public function total(?Carbon $since = null): float
    {
        return collect($this->paidRows($since))->sum('amount');
    }

    /**
     * Earned inside a window, `from` included and `to` excluded.
     *
     * Comparisons need this: „letos" and „loni" are two windows, and taking one
     * from the other by subtracting totals only works while the windows are
     * neatly nested.
     */
    public function between(Carbon $from, Carbon $to): float
    {
        return $this->paidRows($from)
            ->filter(fn (array $row) => $row['at']->lt($to))
            ->sum('amount');
    }

    /**
     * How many subscriptions and memberships were bought in each month.
     *
     * @return array<string, int>
     */
    public function purchasesByMonth(int $months = 12): array
    {
        $buckets = $this->emptyMonths($months);

        foreach ($this->rows(now()->subMonths($months - 1)->startOfMonth()) as $row) {
            $month = $row['at']->format('Y-m');

            if (array_key_exists($month, $buckets)) {
                $buckets[$month]++;
            }
        }

        return $buckets;
    }

    /**
     * How many accounts were created in each month.
     *
     * @return array<string, int>
     */
    public function registrationsByMonth(int $months = 12): array
    {
        $buckets = $this->emptyMonths($months);

        $rows = \App\Models\User::query()
            ->where('created_at', '>=', now()->subMonths($months - 1)->startOfMonth())
            ->get(['created_at']);

        foreach ($rows as $row) {
            $month = $row->created_at?->format('Y-m');

            if ($month !== null && array_key_exists($month, $buckets)) {
                $buckets[$month]++;
            }
        }

        return $buckets;
    }

    /** @return array<string, int> */
    private function emptyMonths(int $months): array
    {
        $buckets = [];
        $cursor = now()->subMonths($months - 1)->startOfMonth();

        while ($cursor->lte(now())) {
            $buckets[$cursor->format('Y-m')] = 0;
            $cursor->addMonth();
        }

        return $buckets;
    }

    public function activeProfileSubscriptions(): int
    {
        return Subscription::query()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where('ends_at', '>', now())
            ->count();
    }

    public function activeMemberships(): int
    {
        return MemberSubscription::query()
            ->where('status', MemberSubscription::STATUS_ACTIVE)
            ->where('ends_at', '>', now())
            ->count();
    }

    /** Running subscriptions that end within the next week. */
    public function expiringWithin(int $days = 7): int
    {
        $until = now()->addDays($days);

        return Subscription::query()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereBetween('ends_at', [now(), $until])
            ->count()
            + MemberSubscription::query()
                ->where('status', MemberSubscription::STATUS_ACTIVE)
                ->whereBetween('ends_at', [now(), $until])
                ->count();
    }

    /** How many were granted without a payment, so the number is explainable. */
    public function grantedWithoutPayment(): int
    {
        return $this->rows(null)
            ->reject(fn (array $row) => $row['paid'])
            ->count();
    }

    /**
     * Paid rows only.
     *
     * @return Collection<int, array{at: Carbon, amount: float, paid: bool}>
     */
    private function paidRows(?Carbon $since): Collection
    {
        return $this->rows($since)->filter(fn (array $row) => $row['paid']);
    }

    /**
     * Every purchase of either product, normalised.
     *
     * @return Collection<int, array{at: Carbon, amount: float, paid: bool}>
     */
    private function rows(?Carbon $since): Collection
    {
        $prices = SubscriptionType::query()->get()->keyBy('id');

        $map = function ($subscription) use ($prices) {
            $metadata = $subscription->metadata ?? [];
            $paid = ! OfflineCheckout::wasManual(is_array($metadata) ? $metadata : []);

            // Stripe reports in minor units; the plan's price is in whole ones.
            $charged = $metadata['amount_total'] ?? null;

            $amount = $charged !== null
                ? ((float) $charged) / 100
                : (float) ($prices[$subscription->subscription_type_id]->price_czk
                    ?? $prices[$subscription->subscription_type_id]->price
                    ?? 0);

            return [
                'at' => $subscription->starts_at ?? $subscription->created_at ?? now(),
                'amount' => $amount,
                'paid' => $paid,
            ];
        };

        $profileRows = Subscription::query()
            ->when($since, fn ($q) => $q->where('starts_at', '>=', $since))
            ->get()
            ->map($map);

        $memberRows = MemberSubscription::query()
            ->when($since, fn ($q) => $q->where('starts_at', '>=', $since))
            ->get()
            ->map($map);

        return $profileRows->concat($memberRows)->values();
    }
}
