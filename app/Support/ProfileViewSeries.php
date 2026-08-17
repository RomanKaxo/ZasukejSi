<?php

namespace App\Support;

use App\Models\ProfileView;
use Illuminate\Support\Carbon;

/**
 * View counts per profile, bucketed for the little in-row chart.
 *
 * The admin used to list `profile_views` row by row — one line per visit, so a
 * busy month meant tens of thousands of rows nobody could read and nothing you
 * could sort „the most viewed girls" by. What an operator actually wants is
 * one line per profile with a number and a shape.
 *
 * Everything here is loaded for the whole page in one query and memoised, so a
 * table of twenty-five rows costs two queries rather than fifty.
 */
class ProfileViewSeries
{
    public const PERIOD_TOTAL = 'total';
    public const PERIOD_MONTH = 'month';
    public const PERIOD_QUARTER = 'quarter';
    public const PERIOD_HALF = 'half';
    public const PERIOD_YEAR = 'year';

    public const DEFAULT_PERIOD = self::PERIOD_YEAR;

    /** @var array<string, array<int, array<string, int>>> */
    private array $memo = [];

    /** @return array<string, string> */
    public static function periods(): array
    {
        return [
            self::PERIOD_TOTAL => 'Celkem',
            self::PERIOD_MONTH => 'Za měsíc',
            self::PERIOD_QUARTER => 'Za čtvrt roku',
            self::PERIOD_HALF => 'Za půl roku',
            self::PERIOD_YEAR => 'Za rok',
        ];
    }

    public static function normalisePeriod(?string $period): string
    {
        return array_key_exists((string) $period, self::periods())
            ? (string) $period
            : self::DEFAULT_PERIOD;
    }

    /** The first day counted for a period, or null for „everything". */
    public static function since(string $period): ?Carbon
    {
        return match (self::normalisePeriod($period)) {
            self::PERIOD_MONTH => now()->subMonth()->startOfDay(),
            self::PERIOD_QUARTER => now()->subMonths(3)->startOfDay(),
            self::PERIOD_HALF => now()->subMonths(6)->startOfDay(),
            self::PERIOD_YEAR => now()->subYear()->startOfDay(),
            default => null,
        };
    }

    /**
     * Whether the chart draws days or months.
     *
     * A year of days is 365 bars in a cell 120px wide, which is a smear. The
     * year is drawn by month; only a one-month period is short enough to draw
     * day by day.
     */
    public static function isDaily(string $period): bool
    {
        return self::normalisePeriod($period) === self::PERIOD_MONTH;
    }

    /**
     * Counts per bucket for every profile on the page.
     *
     * @param  array<int, int>  $profileIds
     * @return array<int, array<string, int>> profile id => bucket => count
     */
    public function buckets(array $profileIds, string $period): array
    {
        $period = self::normalisePeriod($period);

        // Prázdný seznam znamená „všechny profily". Tabulka nemá po ruce
        // seznam řádků stránky ve chvíli, kdy se počítá stav sloupce, a
        // jeden dotaz nad všemi profily je pořád jeden dotaz — na rozdíl od
        // dotazu na každý řádek.
        $key = $period . ':' . ($profileIds === [] ? 'vse' : implode(',', $profileIds));

        if (isset($this->memo[$key])) {
            return $this->memo[$key];
        }

        $daily = self::isDaily($period);

        // The chart always covers a year unless the period itself is shorter,
        // so „celkem" still gets a shape rather than one flat bar.
        $chartSince = $daily ? now()->subMonth()->startOfDay() : now()->subYear()->startOfMonth();

        $rows = ProfileView::query()
            ->when($profileIds !== [], fn ($q) => $q->whereIn('profile_id', $profileIds))
            ->where('viewed_date', '>=', $chartSince->toDateString())
            ->selectRaw('profile_id, viewed_date, COUNT(*) as total')
            ->groupBy('profile_id', 'viewed_date')
            ->get();

        $buckets = [];

        foreach ($rows as $row) {
            $date = Carbon::parse($row->viewed_date);
            $bucket = $daily ? $date->toDateString() : $date->format('Y-m');

            $buckets[$row->profile_id][$bucket] = ($buckets[$row->profile_id][$bucket] ?? 0) + (int) $row->total;
        }

        return $this->memo[$key] = $buckets;
    }

    /**
     * Bucket labels in order, so a profile with no views still draws a line.
     *
     * @return array<int, string>
     */
    public static function axis(string $period): array
    {
        $labels = [];

        if (self::isDaily($period)) {
            $cursor = now()->subMonth()->startOfDay();

            while ($cursor->lte(now())) {
                $labels[] = $cursor->toDateString();
                $cursor->addDay();
            }

            return $labels;
        }

        $cursor = now()->subYear()->startOfMonth();

        while ($cursor->lte(now())) {
            $labels[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        return $labels;
    }

    /**
     * The series drawn for one profile: one value per bucket, gaps as zeroes.
     *
     * @param  array<int, array<string, int>>  $buckets
     * @return array<int, int>
     */
    public static function seriesFor(array $buckets, int $profileId, string $period): array
    {
        $own = $buckets[$profileId] ?? [];

        return array_map(fn (string $label) => (int) ($own[$label] ?? 0), self::axis($period));
    }
}
