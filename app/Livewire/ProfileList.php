<?php

namespace App\Livewire;

use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class ProfileList extends Component
{
    use WithPagination;

    public $loading = false;
    public $perPage = 25;
    
    // Current filters (synced with search component)
    public $region = '';
    public $country = '';
    public $countryCode = '';
    public $ageMin = '';
    public $ageMax = '';
    public $verified = false;
    
    // Quick filter properties
    public $ageGroup = ''; // '18-25', '26-30', '31-35', '36-40', '40-50', '50+'
    public $sortRecommendation = ''; // '', 'desc' (best first), 'asc' (worst first)
    public $hasVerifiedPhoto = false;
    public $hasVideo = false;
    public $isPornActress = false;
    public $sortNew = ''; // '', 'desc' (newest first), 'asc' (oldest first)
    public $hasRating = false; // profiles with rating/reviews
    
    protected $queryString = [
        'region' => ['except' => ''],
        'country' => ['except' => ''],
        'countryCode' => ['except' => '', 'as' => 'country_code'],
        'ageMin' => ['except' => '', 'as' => 'age_min'],
        'ageMax' => ['except' => '', 'as' => 'age_max'],
        'verified' => ['except' => false],
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
        // Set filters from URL parameters
        $this->region = request('region', request('city', ''));
        $this->country = request('country', '');
        $this->countryCode = request('country_code', '');
        $this->ageMin = request('age_min', '');
        $this->ageMax = request('age_max', '');
        $this->verified = request()->boolean('verified');
        
        // Set quick filters from URL
        $this->ageGroup = request('age', '');
        $this->sortRecommendation = request('recommend', '');
        $this->hasVerifiedPhoto = request()->boolean('verified_photo');
        $this->hasVideo = request()->boolean('video');
        $this->isPornActress = request()->boolean('actress');
        $this->sortNew = request('new', '');
        $this->hasRating = request()->boolean('rated');
    }

    /**
     * Listen for search updates from the search component
     */
    #[On('profile-search-updated')]
    public function updateFilters($filters)
    {
        $this->region = $filters['region'] ?? $filters['city'] ?? '';
        $this->ageMin = $filters['age_min'] ?? '';
        $this->ageMax = $filters['age_max'] ?? '';
        $this->verified = $filters['verified'] ?? false;
        
        // Reset pagination
        $this->resetPage();
    }

    public function loadMore()
    {
        $this->perPage += 10;
    }

    public function resetFilters()
    {
        $this->reset(['region', 'ageMin', 'ageMax', 'verified', 'ageGroup', 'sortRecommendation', 'hasVerifiedPhoto', 'hasVideo', 'isPornActress', 'sortNew', 'hasRating']);
        $this->resetPage();
    }

    public function clearLocation()
    {
        $this->reset(['region', 'country', 'countryCode']);
        $this->resetPage();
    }

    /**
     * Toggle quick filter methods
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
    #[Computed]
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

    #[Computed]
    public function profiles()
    {
        if ($this->usesShowcaseProfiles()) {
            // Get showcase profiles (identified by content->is_showcase = true or by emails)
            $showcaseQuery = Profile::with(['user:id,name', 'media'])
                ->approved()
                ->public()
                ->select($this->getPublicProfileColumns())
                ->where('content->is_showcase', true)
                ->orderBy('created_at', 'desc');

            $showcaseProfiles = $showcaseQuery->get();

            // If we have showcase profiles, create a repeated virtual list and paginate it
            if ($showcaseProfiles->count() > 0) {
                // Number of pages to expose in pagination (repeat showcase profiles)
                $pagesCount = 6; // show 6 pages by default
                $total = $this->perPage * $pagesCount;

                // Determine current page (Livewire maintains $this->page when using WithPagination)
                $currentPage = $this->page ?? request()->get('page', 1);

                // Build a large repeated collection to cover total items
                $needed = $total;
                $result = collect();
                while ($result->count() < $needed) {
                    $result = $result->concat($showcaseProfiles);
                }

                // Slice the items for the current page
                $offset = ($currentPage - 1) * $this->perPage;
                $items = $result->slice($offset, $this->perPage)->values();

                // Create a paginator manually with the requested total and current page
                $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                    $items,
                    $total,
                    $this->perPage,
                    $currentPage,
                    ['path' => request()->url(), 'pageName' => 'page']
                );

                return $paginator;
            }
        }

        // Fallback to normal query if no showcase profiles exist
        $query = Profile::with(['user:id,name', 'media'])
            ->approved()
            ->public()
            ->select($this->getPublicProfileColumns())
            ->orderBy('created_at', 'desc');

        // Apply search filters (from search component)
        if ($this->countryCode) {
            $query->whereRaw('LOWER(country_code) = ?', [mb_strtolower($this->countryCode)]);
        }

        if ($this->region) {
            $this->applyRegionFilter($query, $this->region);
        }

        if ($this->ageMin) {
            $query->where('age', '>=', $this->ageMin);
        }

        if ($this->ageMax) {
            $query->where('age', '<=', $this->ageMax);
        }

        if ($this->verified) {
            $query->verified();
        }

        // Apply quick filters
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
            $query->verified();
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

    /**
     * Apply age group filter logic
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

    public function render()
    {
        return view('livewire.profile-list');
    }

    private function usesShowcaseProfiles(): bool
    {
        return $this->region === ''
            && $this->country === ''
            && $this->countryCode === ''
            && $this->ageMin === ''
            && $this->ageMax === ''
            && $this->verified === false
            && $this->ageGroup === ''
            && $this->sortRecommendation === ''
            && $this->hasVerifiedPhoto === false
            && $this->hasVideo === false
            && $this->isPornActress === false
            && $this->sortNew === ''
            && $this->hasRating === false;
    }

    /**
     * Get only necessary columns for public profile view
     */
    private function getPublicProfileColumns(): array
    {
        return [
            'id',
            'user_id',
            'display_name',
            'age', 
            'city',
            'about',
            'verified_at',
            'status',
            'created_at',
            'updated_at'
        ];
    }
}