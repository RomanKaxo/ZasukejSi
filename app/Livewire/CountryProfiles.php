<?php

namespace App\Livewire;

use App\Models\Profile;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class CountryProfiles extends Component
{
    use WithPagination;

    public $selectedCountryCode = null;
    public $selectedRegion = null;
    public $expandedCountries = [];
    public $perPage = 20;
    
    // Search parameter from redirect
    public $region = '';
    
    // Quick filter properties (matching ProfileList)
    public $ageGroup = ''; // '18-25', '26-30', '31-35', '36-40', '40-50', '50+'
    public $sortRecommendation = ''; // '', 'desc' (best first), 'asc' (worst first)
    public $hasVerifiedPhoto = false;
    public $hasVideo = false;
    public $isPornActress = false;
    public $sortNew = ''; // '', 'desc' (newest first), 'asc' (oldest first)
    public $hasRating = false; // profiles with rating/reviews

    protected $paginationTheme = 'bootstrap';
    
    protected $queryString = [
        'region' => ['except' => ''],
        'ageGroup' => ['except' => '', 'as' => 'age'],
        'sortRecommendation' => ['except' => '', 'as' => 'recommend'],
        'hasVerifiedPhoto' => ['except' => false, 'as' => 'verified_photo'],
        'hasVideo' => ['except' => false, 'as' => 'video'],
        'isPornActress' => ['except' => false, 'as' => 'actress'],
        'sortNew' => ['except' => '', 'as' => 'new'],
        'hasRating' => ['except' => false, 'as' => 'rated'],
    ];

    public function mount()
    {
        // Handle search parameters from redirect
        $this->region = request('region', request('city', ''));
        $this->ageGroup = request('age', '');
        $this->sortRecommendation = request('recommend', '');
        $this->hasVerifiedPhoto = request()->boolean('verified_photo');
        $this->hasVideo = request()->boolean('video');
        $this->isPornActress = request()->boolean('actress');
        $this->sortNew = request('new', '');
        $this->hasRating = request()->boolean('rated');
        
        if ($this->region) {
            $this->autoSelectRegionFromSearch($this->region);
        }
    }
    
    /**
     * Auto-select country and region when coming from search.
     */
    private function autoSelectRegionFromSearch($searchRegion)
    {
        $searchRegion = urldecode($searchRegion);

        $match = DB::table('profiles')
            ->join('cities', function ($join) {
                $join->on('cities.country_code', '=', 'profiles.country_code')
                    ->whereRaw('LOWER(cities.name) = LOWER(profiles.city)');
            })
            ->where('profiles.status', 'approved')
            ->where('profiles.is_public', true)
            ->whereNotNull('profiles.verified_at')
            ->whereNotNull('profiles.country_code')
            ->where('cities.admin_name', $searchRegion)
            ->select('profiles.country_code', 'cities.admin_name')
            ->first();

        if (!$match) {
            $match = DB::table('profiles')
                ->join('cities', function ($join) {
                    $join->on('cities.country_code', '=', 'profiles.country_code')
                        ->whereRaw('LOWER(cities.name) = LOWER(profiles.city)');
                })
                ->where('profiles.status', 'approved')
                ->where('profiles.is_public', true)
                ->whereNotNull('profiles.verified_at')
                ->whereNotNull('profiles.country_code')
                ->whereRaw('LOWER(cities.admin_name) LIKE ?', ['%' . mb_strtolower($searchRegion) . '%'])
                ->select('profiles.country_code', 'cities.admin_name')
                ->first();
        }

        if ($match && $match->country_code) {
            $this->selectedCountryCode = $match->country_code;
            $this->selectedRegion = $match->admin_name;
            $this->expandedCountries[] = $match->country_code;
        }
    }


    public function selectCountry($countryCode = null)
    {
        $this->selectedCountryCode = $countryCode;
        $this->selectedRegion = null;
        $this->resetPage();
    }


    public function selectRegion($countryCode, $region = null)
    {
        $this->selectedCountryCode = $countryCode;
        $this->selectedRegion = $region;
        $this->resetPage();
    }
    
    /**
     * Clear the selected location (country and region).
     */
    public function clearLocation()
    {
        $this->selectedCountryCode = null;
        $this->selectedRegion = null;
        $this->region = '';
        $this->expandedCountries = [];
        $this->resetPage();
    }


    public function toggleCountryExpansion($countryCode)
    {
        if (in_array($countryCode, $this->expandedCountries)) {
            $this->expandedCountries = array_diff($this->expandedCountries, [$countryCode]);
        } else {
            $this->expandedCountries[] = $countryCode;
        }
    }

    public function loadMore()
    {
        $this->perPage += 20;
    }
    
    /**
     * Reset all filters
     */
    public function resetFilters()
    {
        $this->reset(['region', 'ageGroup', 'sortRecommendation', 'hasVerifiedPhoto', 'hasVideo', 'isPornActress', 'sortNew', 'hasRating']);
        $this->selectedCountryCode = null;
        $this->selectedRegion = null;
        $this->expandedCountries = [];
        $this->resetPage();
    }

    /**
     * Toggle quick filter methods (matching ProfileList)
     */
    public function toggleAgeGroup($group)
    {
        $this->ageGroup = $this->ageGroup === $group ? '' : $group;
        $this->resetPage();
    }

    public function toggleRecommendation()
    {
        // Cycle through: '' -> 'desc' -> 'asc' -> ''
        if ($this->sortRecommendation === '') {
            $this->sortRecommendation = 'desc';
        } elseif ($this->sortRecommendation === 'desc') {
            $this->sortRecommendation = 'asc';
        } else {
            $this->sortRecommendation = '';
        }
        $this->resetPage();
    }

    public function toggleVerifiedPhoto()
    {
        $this->hasVerifiedPhoto = !$this->hasVerifiedPhoto;
        $this->resetPage();
    }

    public function toggleVideo()
    {
        $this->hasVideo = !$this->hasVideo;
        $this->resetPage();
    }

    public function togglePornActress()
    {
        $this->isPornActress = !$this->isPornActress;
        $this->resetPage();
    }

    public function toggleNew()
    {
        // Cycle through: '' -> 'desc' -> 'asc' -> ''
        if ($this->sortNew === '') {
            $this->sortNew = 'desc';
        } elseif ($this->sortNew === 'desc') {
            $this->sortNew = 'asc';
        } else {
            $this->sortNew = '';
        }
        $this->resetPage();
    }

    public function toggleRating()
    {
        $this->hasRating = !$this->hasRating;
        $this->resetPage();
    }
    
    /**
     * Get active filters count for UI
     */
    public function activeFiltersCount()
    {
        $count = 0;
        if ($this->ageGroup) $count++;
        if ($this->sortRecommendation) $count++;
        if ($this->hasVerifiedPhoto) $count++;
        if ($this->hasVideo) $count++;
        if ($this->isPornActress) $count++;
        if ($this->sortNew) $count++;
        if ($this->hasRating) $count++;
        return $count;
    }

    public function getCountriesProperty()
    {
        if ($this->usesEnglishHomepageMockCountries()) {
            return $this->getEnglishHomepageCountries();
        }

        $codes = include base_path('lang/en/codes.php');
        $regions = DB::table('profiles')
            ->join('cities', function ($join) {
                $join->on('cities.country_code', '=', 'profiles.country_code')
                    ->whereRaw('LOWER(cities.name) = LOWER(profiles.city)');
            })
            ->where('profiles.is_public', true)
            ->whereNotNull('profiles.country_code')
            ->whereNotNull('profiles.verified_at')
            ->whereNotNull('cities.admin_name')
            ->where('cities.admin_name', '!=', '')
            ->select('profiles.country_code', 'cities.admin_name', DB::raw('COUNT(*) as profiles_count'))
            ->groupBy('profiles.country_code', 'cities.admin_name')
            ->get();

        if ($regions->isEmpty()) {
            return DB::table('profiles')
                ->where('profiles.is_public', true)
                ->whereNotNull('profiles.country_code')
                ->whereNotNull('profiles.verified_at')
                ->select('profiles.country_code', DB::raw('COUNT(*) as profiles_count'))
                ->groupBy('profiles.country_code')
                ->get()
                ->map(function ($country) use ($codes) {
                    return (object) [
                        'country_code' => $country->country_code,
                        'country_name' => $codes[strtolower($country->country_code)] ?? $country->country_code,
                        'profiles_count' => $country->profiles_count,
                        'regions' => collect(),
                    ];
                })
                ->sortBy('country_name')
                ->values();
        }

        return $regions
            ->groupBy('country_code')
            ->map(function ($regionsInCountry, $code) use ($codes) {
                return (object) [
                    'country_code' => $code,
                    'country_name' => $codes[strtolower($code)] ?? $code,
                    'profiles_count' => $regionsInCountry->sum('profiles_count'),
                    'regions' => $regionsInCountry
                        ->map(fn ($region) => [
                            'region' => $region->admin_name,
                            'profiles_count' => $region->profiles_count,
                        ])
                        ->sortBy(fn (array $region) => $this->regionSortKey($region['region']), SORT_NATURAL | SORT_FLAG_CASE)
                        ->values(),
                ];
            })
            ->sortBy('country_name')
            ->values();
    }

    private function usesEnglishHomepageMockCountries(): bool
    {
        return app()->getLocale() === 'en' && request()->routeIs('profiles.index');
    }

    private function getEnglishHomepageCountries()
    {
        $primaryCountries = [
            ['country_code' => 'al', 'country_name' => 'Albánie', 'profiles_count' => 484, 'regions' => []],
            ['country_code' => 'ad', 'country_name' => 'Andorra', 'profiles_count' => 45, 'regions' => []],
            ['country_code' => 'am', 'country_name' => 'Arménie', 'profiles_count' => 24, 'regions' => []],
            ['country_code' => 'be', 'country_name' => 'Belgie', 'profiles_count' => 114, 'regions' => []],
            ['country_code' => 'by', 'country_name' => 'Bělorusko', 'profiles_count' => 20, 'regions' => []],
            [
                'country_code' => 'ba',
                'country_name' => 'Bosna a Hercegovina',
                'profiles_count' => 50,
                'regions' => [
                    ['region' => 'Bihać', 'profiles_count' => 484],
                    ['region' => 'Brčko', 'profiles_count' => 45],
                    ['region' => 'Doboj', 'profiles_count' => 24],
                    ['region' => 'Foča', 'profiles_count' => 114],
                    ['region' => 'Jahorina', 'profiles_count' => 457],
                    ['region' => 'Konjic', 'profiles_count' => 87],
                    ['region' => 'Neum', 'profiles_count' => 70],
                    ['region' => 'Prijedor', 'profiles_count' => 457],
                    ['region' => 'Šamac', 'profiles_count' => 87],
                ],
            ],
            ['country_code' => 'bg', 'country_name' => 'Bulharsko', 'profiles_count' => 457, 'regions' => []],
            ['country_code' => 'me', 'country_name' => 'Černá Hora', 'profiles_count' => 87, 'regions' => []],
            ['country_code' => 'cz', 'country_name' => 'Česká republika', 'profiles_count' => 70, 'regions' => []],
        ];

        $repeatedCountries = [
            ['country_code' => 'al', 'country_name' => 'Albánie', 'profiles_count' => 484, 'regions' => []],
            ['country_code' => 'ad', 'country_name' => 'Andorra', 'profiles_count' => 45, 'regions' => []],
            ['country_code' => 'am', 'country_name' => 'Arménie', 'profiles_count' => 24, 'regions' => []],
            ['country_code' => 'be', 'country_name' => 'Belgie', 'profiles_count' => 114, 'regions' => []],
            ['country_code' => 'by', 'country_name' => 'Bělorusko', 'profiles_count' => 20, 'regions' => []],
            ['country_code' => 'bg', 'country_name' => 'Bulharsko', 'profiles_count' => 457, 'regions' => []],
            ['country_code' => 'me', 'country_name' => 'Černá Hora', 'profiles_count' => 87, 'regions' => []],
            ['country_code' => 'cz', 'country_name' => 'Česká republika', 'profiles_count' => 70, 'regions' => []],
        ];

        return collect($primaryCountries)
            ->concat($repeatedCountries)
            ->concat($repeatedCountries)
            ->map(function (array $country) {
                return (object) [
                    'country_code' => $country['country_code'],
                    'country_name' => $country['country_name'],
                    'profiles_count' => $country['profiles_count'],
                    'regions' => collect($country['regions'] ?? []),
                ];
            })
            ->values();
    }

    public function getProfilesProperty()
    {
        $query = Profile::with(['user:id,name', 'media'])
            ->where('status', 'approved')
            ->where('is_public', true)
            ->whereNotNull('verified_at')
            ->orderBy('created_at', 'desc');

        // Location filters
        if ($this->selectedCountryCode) {
            $query->where('country_code', $this->selectedCountryCode);
        }

        if ($this->selectedRegion) {
            $this->applyRegionFilter($query, $this->selectedRegion);
        }
        
        // Apply quick filters (matching ProfileList logic)
        if ($this->ageGroup) {
            $this->applyAgeGroupFilter($query, $this->ageGroup);
        }

        if ($this->sortRecommendation) {
            // Sort by: 1) VIP status, 2) average rating, 3) newest
            $sortDirection = $this->sortRecommendation === 'desc' ? 'desc' : 'asc';
            $reverseSortDirection = $this->sortRecommendation === 'desc' ? 'asc' : 'desc';
            
            $query->withAvg('ratings', 'rating')
                  ->withExists('activeSubscription as is_vip')
                  ->orderBy('is_vip', $sortDirection)
                  ->orderBy('ratings_avg_rating', $sortDirection)
                  ->orderBy('created_at', $reverseSortDirection);
        }

        if ($this->hasVerifiedPhoto) {
            $query->whereNotNull('verified_at');
        }

        if ($this->hasVideo) {
            // Filter profiles that have video media in their profile-images collection
            $query->whereHas('media', function($q) {
                $q->where('collection_name', 'profile-images')
                  ->where('mime_type', 'like', 'video/%');
            });
        }

        if ($this->isPornActress) {
            $query->where('is_porn_actress', true);
        }

        if ($this->sortNew) {
            // Sort by created_at (newest/oldest)
            $query->orderBy('created_at', $this->sortNew === 'desc' ? 'desc' : 'asc');
        }

        if ($this->hasRating) {
            // Filter profiles that have at least one rating and order by most rated (rating count)
            $query->withCount('ratings')
                  ->whereHas('ratings')
                  ->orderBy('ratings_count', 'desc');
        }

        return $query->paginate($this->perPage);
    }

    protected function applyRegionFilter($query, string $region): void
    {
        $query->whereExists(function ($subQuery) use ($region) {
            $subQuery->select(DB::raw(1))
                ->from('cities')
                ->whereColumn('cities.country_code', 'profiles.country_code')
                ->whereRaw('LOWER(cities.name) = LOWER(profiles.city)')
                ->where('cities.admin_name', $region);
        });
    }

    protected function regionSortKey(string $region): string
    {
        $normalizedRegion = mb_strtolower($region);

        if (in_array($normalizedRegion, ['praha', 'hlavní město praha', 'hlavni mesto praha'], true)) {
            return '0';
        }

        return '1-' . $normalizedRegion;
    }
    
    /**
     * Apply age group filter logic (matching ProfileList)
     */
    private function applyAgeGroupFilter($query, $ageGroup)
    {
        switch ($ageGroup) {
            case '18-25':
                $query->whereBetween('age', [18, 25]);
                break;
            case '26-30':
                $query->whereBetween('age', [26, 30]);
                break;
            case '31-35':
                $query->whereBetween('age', [31, 35]);
                break;
            case '36-40':
                $query->whereBetween('age', [36, 40]);
                break;
            case '40-50':
                $query->whereBetween('age', [40, 50]);
                break;
            case '50+':
                $query->where('age', '>=', 50);
                break;
        }
    }


    public function getSelectedCountryProperty()
    {
        if (!$this->selectedCountryCode) {
            return null;
        }

        $selectedCountry = $this->countries->firstWhere('country_code', $this->selectedCountryCode);

        if ($selectedCountry) {
            return (object) [
                'country_code' => $selectedCountry->country_code,
                'country_name' => $selectedCountry->country_name,
            ];
        }

        $codes = include base_path('lang/en/codes.php');
        return (object) [
            'country_code' => $this->selectedCountryCode,
            'country_name' => $codes[strtolower($this->selectedCountryCode)] ?? $this->selectedCountryCode,
        ];
    }

    public function render()
    {
        return view('livewire.country-profiles', [
            'countries' => $this->countries,
            'profiles' => $this->profiles,
            'selectedCountry' => $this->selectedCountry,
        ]);
    }
}
