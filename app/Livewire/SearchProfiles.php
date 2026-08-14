<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Models\City;
use App\Models\User;
use App\Services\CountryStatsService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class SearchProfiles extends Component
{
    // Search filters
    public $region = '';
    public $age_range = '';
    public $country = '';
    public $country_code = '';

    // UI state
    public $showRegionDropdown = false;
    public $showAgeRangeDropdown = false;

    public function mount()
    {
        if ($this->usesEnglishLocationSearch()) {
            $defaultCountry = $this->englishCountries[0] ?? ['name' => '', 'code' => ''];
            $resolvedCountry = $this->resolveEnglishCountry(
                request('country', ''),
                request('country_code', '')
            ) ?? $defaultCountry;

            $this->country = request('country', $resolvedCountry['name'] ?? '');
            $this->country_code = request('country_code', $resolvedCountry['code'] ?? '');
            $this->region = request('region', '');

            if ($this->country === '' && isset($resolvedCountry['name'])) {
                $this->country = $resolvedCountry['name'];
            }

            if ($this->country_code === '' && isset($resolvedCountry['code'])) {
                $this->country_code = $resolvedCountry['code'];
            }

            return;
        }

        $this->region = request('region', request('city', 'Praha'));
        $this->age_range = request('age', '18-25');
    }

    protected function usesEnglishLocationSearch(): bool
    {
        return app()->getLocale() === 'en';
    }

    /**
     * Countries offered in the location search.
     *
     * This used to be a hardcoded array of nine countries with invented counts,
     * disagreeing with the two other hardcoded country lists elsewhere in the
     * app. It now reads from CountryStatsService, the single source of truth.
     *
     * The returned shape (lowercase `code`, `name`, `count`, `regions` as plain
     * strings) is preserved so the Blade template stays untouched.
     */
    protected function englishCountriesData(): array
    {
        return app(CountryStatsService::class)
            ->countries()
            ->map(fn ($country) => [
                // The template builds flagcdn URLs from this, which need lowercase.
                'code' => strtolower($country->code),
                'name' => $country->name,
                'count' => $country->profiles_count,
                'regions' => $country->regions->pluck('name')->all(),
            ])
            ->all();
    }

    protected function resolveEnglishCountry(?string $name, ?string $code): ?array
    {
        foreach ($this->englishCountriesData() as $country) {
            if ($code !== null && $code !== '' && $country['code'] === $code) {
                return $country;
            }

            if ($name !== null && $name !== '' && $country['name'] === $name) {
                return $country;
            }
        }

        return null;
    }

    public function getEnglishCountriesProperty()
    {
        return $this->englishCountriesData();
    }

    /**
     * Registered provider/member counts shown in the search hero badges.
     *
     * These were printed as the hardcoded "1 420" and "382". They are cached
     * briefly because the badges render on every page and the numbers move
     * slowly.
     */
    public function getGirlsCountProperty(): int
    {
        return $this->registeredCount('female');
    }

    public function getGentsCountProperty(): int
    {
        return $this->registeredCount('male');
    }

    private function registeredCount(string $gender): int
    {
        return Cache::remember(
            "registered-users-count:{$gender}",
            now()->addMinutes(5),
            fn () => User::where('gender', $gender)->count()
        );
    }

    /**
     * Czech regions list shown in homepage search.
     */
    public function getAllRegionsProperty()
    {
        return [
            'Praha',
            'Středočeský Kraj',
            'Jihočeský Kraj',
            'Plzeňský Kraj',
            'Karlovarský Kraj',
            'Ústecký Kraj',
            'Liberecký Kraj',
            'Královéhradecký Kraj',
            'Pardubický Kraj',
            'Vysočina',
            'Jihomoravský Kraj',
            'Olomoucký Kraj',
            'Zlínský Kraj',
            'Moravskoslezský Kraj',
        ];
    }

    public function updatedRegion()
    {
        $this->showRegionDropdown = true;
    }

    public function selectRegion($region)
    {
        $this->region = $region;
        $this->showRegionDropdown = false;
    }

    public function showDropdown()
    {
        $this->showRegionDropdown = true;
    }

    public function clearAndShowDropdown()
    {
        $this->region = '';
        $this->showRegionDropdown = true;
    }

    // Age Range methods
    public function clearAndShowAgeRangeDropdown()
    {
        $this->age_range = '';
        $this->showAgeRangeDropdown = true;
    }

    public function selectAgeRange($ageRange)
    {
        $this->age_range = $ageRange;
        $this->showAgeRangeDropdown = false;
    }

    public function getFilteredRegionsProperty()
    {
        $regions = $this->allRegions;
        
        if (empty($this->region)) {
            return $regions;
        }

        return collect($regions)
            ->filter(fn ($regionOption) => str_contains(mb_strtolower($regionOption), mb_strtolower($this->region)))
            ->values()
            ->toArray();
    }

    public function getAgeRangeOptionsProperty()
    {
        return [
            '18-25' => Lang::get('front.profiles.list.age_18_25'),
            '26-30' => Lang::get('front.profiles.list.age_26_30'),
            '31-35' => Lang::get('front.profiles.list.age_31_35'),
            '36-40' => Lang::get('front.profiles.list.age_36_40'),
            '40-50' => Lang::get('front.profiles.list.age_40_50'),
            '50+' => Lang::get('front.profiles.list.age_50_plus'),
        ];
    }

    /**
     * Return towns/regions available for the currently selected country.
     * Used by the frontend to populate the town dropdown based on country.
     */
    public function getCountryTownsProperty(): array
    {
        if (!$this->usesEnglishLocationSearch()) {
            return [];
        }

        $code = $this->country_code ?: null;
        if (!$code) {
            return [];
        }

        // Prefer the regions that actually hold profiles for this country; only
        // fall back to the full admin_name/city list when none do, so the picker
        // leads somewhere useful rather than to empty result pages.
        foreach ($this->englishCountriesData() as $country) {
            if ((isset($country['code']) && strcasecmp($country['code'], $code) === 0)
                || (isset($country['name']) && $country['name'] === $this->country)) {
                if (!empty($country['regions'])) {
                    return $country['regions'];
                }
                break;
            }
        }

        $countryCodeUpper = strtoupper($code);

        // Try to fetch admin_name (region) list for the country from cities table
        $towns = DB::table('cities')
            ->where('country_code', $countryCodeUpper)
            ->whereNotNull('admin_name')
            ->where('admin_name', '!=', '')
            ->groupBy('admin_name')
            ->orderBy('admin_name')
            ->pluck('admin_name')
            ->toArray();

        // Fallback to city names if admin_name not available
        if (empty($towns)) {
            $towns = City::forCountry($countryCodeUpper)
                ->orderBy('name')
                ->limit(200)
                ->pluck('name')
                ->toArray();
        }

        return $towns;
    }

    /**
     * Execute search - redirect to countries page with filters
     */
    public function search()
    {
        $params = [
            'locale' => app()->getLocale(),
        ];

        if ($this->usesEnglishLocationSearch()) {
            if ($this->country) {
                $params['country'] = $this->country;
            }

            if ($this->country_code) {
                $params['country_code'] = $this->country_code;
            }

            if ($this->region) {
                $params['region'] = $this->region;
            }

            return $this->redirect(route('countries.index', $params));
        }
        
        if ($this->region) {
            $params['region'] = $this->region;
        }
        
        if ($this->age_range) {
            $params['age'] = $this->age_range;
        }

        return $this->redirect(route('countries.index', $params));
    }

    public function render()
    {
        return view('livewire.search-profiles');
    }
}
