<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Models\City;
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

        $this->region = request('region', request('city', 'Hlavní město Praha'));
        $this->age_range = request('age', '18');
    }

    protected function usesEnglishLocationSearch(): bool
    {
        return app()->getLocale() === 'en';
    }

    protected function englishCountriesData(): array
    {
        return [
            [
                'code' => 'ba',
                'name' => 'Bosna a Hercegovina',
                'count' => 50,
                'regions' => ['Bihać', 'Brčko', 'Doboj', 'Foča', 'Jahorina', 'Konjic', 'Neum', 'Prijedor', 'Šamac'],
            ],
            ['code' => 'al', 'name' => 'Albánie', 'count' => 484, 'regions' => []],
            ['code' => 'ad', 'name' => 'Andorra', 'count' => 45, 'regions' => []],
            ['code' => 'am', 'name' => 'Arménie', 'count' => 24, 'regions' => []],
            ['code' => 'be', 'name' => 'Belgie', 'count' => 114, 'regions' => []],
            ['code' => 'by', 'name' => 'Bělorusko', 'count' => 20, 'regions' => []],
            ['code' => 'bg', 'name' => 'Bulharsko', 'count' => 457, 'regions' => []],
            ['code' => 'me', 'name' => 'Černá Hora', 'count' => 87, 'regions' => []],
            ['code' => 'cz', 'name' => 'Česká republika', 'count' => 70, 'regions' => []],
        ];
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
     * Czech regions list shown in homepage search.
     */
    public function getAllRegionsProperty()
    {
        return [
            'Hlavní město Praha',
            'Středočeský kraj',
            'Jihočeský kraj',
            'Plzeňský kraj',
            'Karlovarský kraj',
            'Ústecký kraj',
            'Liberecký kraj',
            'Královéhradecký kraj',
            'Pardubický kraj',
            'Vysočina',
            'Jihomoravský kraj',
            'Olomoucký kraj',
            'Zlínský kraj',
            'Moravskoslezský kraj',
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
        $yearsLabel = Lang::get('front.profiles.list.years');

        return [
            '18' => "18 {$yearsLabel}",
            '19' => "19 {$yearsLabel}",
            '20' => "20 {$yearsLabel}",
            '21' => "21 {$yearsLabel}",
            '22' => "22 {$yearsLabel}",
            '23' => "23 {$yearsLabel}",
            '24' => "24 {$yearsLabel}",
            '25' => "25 {$yearsLabel}",
            '26' => "26 {$yearsLabel}",
            '27' => "27 {$yearsLabel}",
            '28' => "28 {$yearsLabel}",
            '29' => "29 {$yearsLabel}",
            '30' => "30 {$yearsLabel}",
            '35' => "35 {$yearsLabel}",
            '40' => "40 {$yearsLabel}",
            '45' => "45 {$yearsLabel}",
            '50' => "50 {$yearsLabel}",
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

        // prefer static regions declared for the mock english countries
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
