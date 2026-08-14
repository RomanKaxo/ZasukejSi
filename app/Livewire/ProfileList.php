<?php

namespace App\Livewire;

use App\Models\Profile;
use App\Models\ProfileView;
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
    public $segmentId = '';

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
        'segmentId' => ['except' => '', 'as' => 'segment'],
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
        $this->segmentId = request('segment', '');
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
        $this->reset(['region', 'ageMin', 'ageMax', 'verified', 'ageGroup', 'sortRecommendation', 'hasVerifiedPhoto', 'hasVideo', 'isPornActress', 'sortNew', 'hasRating', 'segmentId']);
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

    public function toggleSegment($segmentId)
    {
        $this->segmentId = $this->segmentId == $segmentId ? '' : $segmentId;
        $this->resetPage();
    }

    public function updatedSegmentId(): void
    {
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
        if ($this->segmentId) $count++;
        return $count;
    }

    #[Computed]
    public function availableSegments()
    {
        return \App\Models\Segment::active()->ordered()->get();
    }

    /**
     * The two posts shown in the "latest news" block under the listing.
     *
     * That block used to be two hardcoded cards with a fixed date, a fixed
     * reading time and filler copy. Posts are managed in the admin under
     * "Blog příspěvky" (Page with type = blog).
     */
    #[Computed]
    public function latestPosts()
    {
        return \App\Models\Page::blog()
            ->published()
            ->with('media')
            ->latest()
            ->take(2)
            ->get();
    }

    #[Computed]
    public function profiles()
    {
        $query = Profile::with(['user:id,name,last_activity', 'media', 'segments'])
            ->approved()
            ->public()
            ->select($this->getPublicProfileColumns());

        // Featured profiles lead the unfiltered homepage. This used to be done by
        // taking the featured profiles, repeating them until they filled six
        // pages, and handing the paginator a fabricated total (perPage * 6) — so
        // the homepage advertised 150 results built from five profiles shown over
        // and over, and no other profile was reachable from it at all.
        //
        // Now it is purely an ordering hint: featured profiles sort first, every
        // real profile is still in the list, and the total is the real count.
        // The flag is editable per profile in the admin (ProfileResource).
        if ($this->usesShowcaseProfiles()) {
            $query->orderByDesc('content->is_showcase');
        }

        $query->orderBy('created_at', 'desc');

        // Apply search filters (from search component)
        if ($this->countryCode) {
            // Codes are stored uppercase (Profile::setCountryCodeAttribute), so a
            // plain equality can use the index — LOWER() forced a full scan.
            $query->where('country_code', strtoupper($this->countryCode));
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
            
            $query->withAvg('ratings', 'percentage')
                  ->withExists('activeSubscription as is_vip')
                  ->orderBy('is_vip', $sortDirection)
                  ->orderBy('ratings_avg_percentage', $sortDirection)
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

        if ($this->segmentId) {
            $query->whereHas('segments', function ($q) {
                $q->where('segments.id', $this->segmentId);
            });
        }

        $profiles = $query->paginate($this->perPage);
        if (app()->environment('local') || request()->query('debug_profiles')) {
            logger()->debug('ProfileList profiles count: '.$profiles->count().' total: '.$profiles->total().' page: '.$profiles->currentPage());
        }
        return $profiles;
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
        // Impressions are recorded here, over the page that is actually being
        // rendered. ProfileController@index used to run its own separate query
        // and log impressions for that result set instead — a different set of
        // profiles from the ones this component then displayed, which made
        // `profile_views` wrong at the source and the provider statistics along
        // with it.
        $this->recordImpressions();

        return view('livewire.profile-list');
    }

    /**
     * Log one impression per profile visible on the current page.
     *
     * A provider's own profile is excluded so browsing the site does not inflate
     * her own numbers — matching the rule ProfileController already applied to
     * click views.
     */
    private function recordImpressions(): void
    {
        // Property access, not profiles() — a direct method call bypasses the
        // #[Computed] memoisation and would run the whole listing query a
        // second time on every render.
        $profileIds = collect($this->profiles->items())->pluck('id')->all();

        $user = auth()->user();
        if ($user && $user->isFemale() && $user->profile) {
            $profileIds = array_values(array_filter(
                $profileIds,
                fn ($id) => $id !== $user->profile->id
            ));
        }

        if ($profileIds !== []) {
            ProfileView::recordImpressions($profileIds);
        }
    }

    /**
     * True when the visitor has not narrowed the list in any way — the state in
     * which featured profiles are allowed to sort to the front.
     */
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
            && $this->hasRating === false
            && $this->segmentId === '';
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
            // The card reads card_height_cm / card_location out of this JSON
            // column. Without it every profile fell back to a placeholder height
            // even when it had a real one stored.
            'content',
            'verified_at',
            'status',
            'created_at',
            'updated_at'
        ];
    }
}