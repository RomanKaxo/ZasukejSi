<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The single source of truth for the country/region lists shown on the site.
 *
 * It replaces three independent hardcoded arrays that disagreed with each other
 * and with the database:
 *   - CountryProfiles::getEnglishHomepageCountries()  (8 countries, listed 3x)
 *   - SearchProfiles::englishCountriesData()          (9 countries)
 *   - components/english-country-sidebar.blade.php    (24 entries = 8 countries
 *     repeated 3x, each repetition with a different count)
 *
 * Composition of the data:
 *   - which countries appear, and in what order  -> `countries` table (admin)
 *   - country names                              -> lang/{cs,en}/codes.php
 *   - regions                                    -> cities.admin_name
 *   - counts                                     -> live query over `profiles`
 *
 * IMPORTANT: the counts use exactly the same predicate as the listing that the
 * user lands on (CountryProfiles::getProfilesProperty) — approved, public and
 * verified. If those diverge, the sidebar promises a number the listing cannot
 * deliver.
 */
class CountryStatsService
{
    private const CACHE_KEY = 'country-stats:v1';

    /**
     * Counts move only when a profile is created, approved, hidden or deleted,
     * and the lists render on every public page — so a short TTL is worth far
     * more than it costs. Profile::booted() flushes this on write, so the TTL is
     * only a backstop for changes made outside Eloquent (raw SQL, imports).
     */
    private const CACHE_TTL_SECONDS = 300;

    /**
     * Locales the site serves. Names are locale-dependent, so the lists are
     * cached per locale. Sourced from config/locales.php.
     *
     * @return array<int, string>
     */
    public static function locales(): array
    {
        return \App\Support\Locales::codes();
    }

    /**
     * Visible countries in admin order, each with its real profile count and its
     * regions.
     *
     * A country with zero profiles is still returned: the list is a stable
     * navigation element, not a stock report. Its count is simply 0.
     *
     * @return Collection<int, object{code: string, name: string, profiles_count: int, regions: Collection}>
     */
    public function countries(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY . ':' . app()->getLocale(),
            self::CACHE_TTL_SECONDS,
            fn () => $this->buildCountries()
        );
    }

    /**
     * Regions of one country that actually hold profiles, ordered with the
     * capital first (mirroring the ordering the listing already used).
     *
     * @return Collection<int, object{name: string, profiles_count: int}>
     */
    public function regionsFor(string $code): Collection
    {
        return $this->countries()
            ->firstWhere('code', strtoupper($code))
            ?->regions ?? collect();
    }

    /**
     * Drop the memoised lists. Called from Profile::booted() on save/delete.
     */
    public static function flush(): void
    {
        foreach (array_unique([...self::locales(), app()->getLocale()]) as $locale) {
            Cache::forget(self::CACHE_KEY . ':' . $locale);
        }
    }

    private function buildCountries(): Collection
    {
        $countries = Country::visible()->ordered()->get();

        if ($countries->isEmpty()) {
            return collect();
        }

        $codes = $countries->pluck('code')->all();
        $counts = $this->profileCountsByCountry($codes);
        $regions = $this->regionCountsByCountry($codes);

        return $countries->map(fn (Country $country) => (object) [
            'code' => $country->code,
            'name' => $country->display_name,
            'profiles_count' => (int) ($counts[$country->code] ?? 0),
            'regions' => $regions->get($country->code, collect()),
        ])->values();
    }

    /**
     * @param  array<int, string>  $codes
     * @return array<string, int>
     */
    private function profileCountsByCountry(array $codes): array
    {
        return $this->publicProfiles()
            ->whereIn('country_code', $codes)
            ->groupBy('country_code')
            ->selectRaw('country_code, COUNT(*) as aggregate')
            ->pluck('aggregate', 'country_code')
            ->all();
    }

    /**
     * Region buckets come from `cities.admin_name`, matched to the profile's
     * free-text `city`. The city column is chosen through an autocomplete
     * against this same table, so the join is reliable in practice; profiles
     * whose city has no match simply carry no region.
     *
     * @param  array<int, string>  $codes
     * @return Collection<string, Collection<int, object>>
     */
    private function regionCountsByCountry(array $codes): Collection
    {
        $rows = $this->publicProfiles()
            ->join('cities', function ($join) {
                $join->on('cities.country_code', '=', 'profiles.country_code')
                    ->whereRaw('LOWER(cities.name) = LOWER(profiles.city)');
            })
            ->whereIn('profiles.country_code', $codes)
            ->whereNotNull('cities.admin_name')
            ->where('cities.admin_name', '!=', '')
            ->groupBy('profiles.country_code', 'cities.admin_name')
            ->selectRaw('profiles.country_code as country_code, cities.admin_name as region, COUNT(*) as aggregate')
            ->get();

        return $rows
            ->groupBy('country_code')
            ->map(fn (Collection $group) => $group
                ->map(fn ($row) => (object) [
                    'name' => $row->region,
                    'profiles_count' => (int) $row->aggregate,
                ])
                ->sortBy(fn ($region) => $this->regionSortKey($region->name), SORT_NATURAL | SORT_FLAG_CASE)
                ->values());
    }

    /**
     * The public listing shows approved + public + verified profiles. Counts
     * must agree with it exactly.
     */
    private function publicProfiles()
    {
        return DB::table('profiles')
            ->where('profiles.status', 'approved')
            ->where('profiles.is_public', true)
            ->whereNotNull('profiles.verified_at')
            ->whereNull('profiles.deleted_at');
    }

    /**
     * Praha sorts first, everything else alphabetically — preserving the
     * ordering the country listing already used.
     */
    private function regionSortKey(string $region): string
    {
        return mb_strtolower($region) === 'praha' ? '0' : '1-' . mb_strtolower($region);
    }
}
