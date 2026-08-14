<?php

namespace App\Livewire;

use App\Models\Profile;
use App\Models\ProfileView;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * The provider's traffic chart.
 *
 * This used to be a mock-up: sixteen fixed September labels ('10. 9.' … '25. 9.')
 * and two hardcoded value arrays, one per variant. A provider paying for a VIP
 * listing was shown invented numbers, and a user without a profile was shown
 * `Profile::first()` — somebody else's.
 *
 * Everything now comes from the `profile_views` table, which the site has been
 * writing all along through ProfileView::recordClick() and
 * ::recordImpressions(); ProfileView::getDailyStats()/getTotalStats() existed
 * for exactly this and were never called.
 *
 *   variant 'homepage' -> impressions (the card appeared in a listing)
 *   variant 'detail'   -> clicks      (the detail page was opened)
 */
class ProfileStatistics extends Component
{
    public ?int $profileId = null;
    public string $variant = 'homepage';
    public string $instanceId = '';

    /** Month being displayed, as Y-m. Kept as a string so Livewire can hydrate it. */
    public string $month = '';

    public int $yAxisMax = 10;
    public int $yAxisStep = 2;
    public array $chartLabels = [];
    public array $chartValues = [];
    public array $chartColors = [];
    public array $chartVip = [];

    public function mount(string $variant = 'homepage'): void
    {
        $this->variant = $variant === 'detail' ? 'detail' : 'homepage';
        $this->instanceId = (string) Str::uuid();
        $this->month = now()->startOfMonth()->format('Y-m');

        // No fallback to an arbitrary profile: a user without one has no
        // statistics, and the view renders its empty state.
        $this->profileId = auth()->user()?->profile?->id;

        $this->buildChart();
    }

    /**
     * Livewire memoises getXProperty() accessors for the duration of a request,
     * so buildChart() must not read through this one — after previousMonth()
     * changes $month the memoised value would still be the old month, and the
     * chart would silently rebuild the range it already had. Templates are free
     * to use it; internal callers use monthStart().
     */
    public function getCurrentMonthProperty(): Carbon
    {
        return $this->monthStart();
    }

    private function monthStart(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
    }

    public function getProfileProperty(): ?Profile
    {
        return $this->profileId ? Profile::find($this->profileId) : null;
    }

    public function getHasProfileProperty(): bool
    {
        return $this->profileId !== null;
    }

    /**
     * Month navigation. The two arrows in the template were inert markup —
     * no handler of any kind — so the chart could never show anything but the
     * current month.
     */
    public function previousMonth(): void
    {
        $this->month = $this->monthStart()->subMonthNoOverflow()->format('Y-m');
        $this->buildChart();
    }

    public function nextMonth(): void
    {
        $next = $this->monthStart()->addMonthNoOverflow();

        // There is nothing to show beyond the current month.
        if ($next->greaterThan(now()->startOfMonth())) {
            return;
        }

        $this->month = $next->format('Y-m');
        $this->buildChart();
    }

    public function getIsCurrentMonthProperty(): bool
    {
        return $this->monthStart()->isSameMonth(now());
    }

    public function getTotalViewsProperty(): int
    {
        return array_sum($this->chartValues);
    }

    private function buildChart(): void
    {
        $start = $this->monthStart();
        // Never chart into the future: the current month stops at today.
        $end = $start->copy()->endOfMonth()->min(now()->endOfDay());

        $this->chartLabels = [];
        $this->chartValues = [];
        $this->chartColors = [];
        $this->chartVip = [];

        if (! $this->profileId || $end->lessThan($start)) {
            $this->yAxisMax = 10;
            $this->yAxisStep = 2;

            return;
        }

        $daily = ProfileView::getDailyStats(
            $this->profileId,
            $start->toDateString(),
            $end->toDateString(),
            $this->variant === 'detail' ? ProfileView::TYPE_CLICK : ProfileView::TYPE_IMPRESSION
        );

        $vipDays = $this->vipDays($start, $end);

        for ($day = $start->copy(); $day->lessThanOrEqualTo($end); $day->addDay()) {
            $key = $day->toDateString();

            $this->chartLabels[] = $day->format('j. n.');
            $this->chartValues[] = (int) ($daily[$key] ?? 0);
            // Keeps the two-tone bar styling the design uses, now anchored to
            // the middle of the month rather than a fixed index.
            $this->chartColors[] = $day->day <= 15 ? '#DD3888' : '#5C2D62';
            $this->chartVip[] = in_array($key, $vipDays, true);
        }

        $this->applyAxisScale();
    }

    /**
     * Days within the range covered by an active subscription, so the VIP
     * marker on the chart reflects when the profile was actually promoted.
     *
     * @return array<int, string>
     */
    private function vipDays(Carbon $start, Carbon $end): array
    {
        $subscriptions = Subscription::query()
            ->where('profile_id', $this->profileId)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where('starts_at', '<=', $end)
            ->where('ends_at', '>=', $start)
            ->get(['starts_at', 'ends_at']);

        if ($subscriptions->isEmpty()) {
            return [];
        }

        $days = [];

        for ($day = $start->copy(); $day->lessThanOrEqualTo($end); $day->addDay()) {
            foreach ($subscriptions as $subscription) {
                if ($day->betweenIncluded($subscription->starts_at, $subscription->ends_at)) {
                    $days[] = $day->toDateString();
                    break;
                }
            }
        }

        return $days;
    }

    /**
     * Round the axis up to a readable step so the tallest bar never touches the
     * top of the plot. The old code hardcoded 120/20 and 30/5 to match its
     * hardcoded values.
     */
    private function applyAxisScale(): void
    {
        $max = max($this->chartValues ?: [0]);

        if ($max <= 0) {
            $this->yAxisMax = 10;
            $this->yAxisStep = 2;

            return;
        }

        $step = (int) max(1, 10 ** floor(log10(max($max, 1))) / 2);
        $this->yAxisMax = (int) (ceil($max / $step) * $step);

        if ($this->yAxisMax <= $max) {
            $this->yAxisMax += $step;
        }

        $this->yAxisStep = $step;
    }

    public function render()
    {
        return view('livewire.profile-statistics');
    }
}
