<?php

namespace App\Livewire;

use App\Models\Profile;
use Livewire\Component;
use Livewire\Attributes\Computed;

class ProfileSlider extends Component
{
    // Filter options
    public bool $vipOnly = false;
    public bool $verifiedOnly = false;
    public bool $hasVideoOnly = false;
    public bool $pornActressOnly = false;
    public ?string $city = null;
    public ?int $ageMin = null;
    public ?int $ageMax = null;
    public ?string $ageGroup = null;
    
    // Sorting options
    public string $sortBy = 'created_at'; // created_at, rating, recommendation
    public string $sortDirection = 'desc'; // asc, desc
    
    // Limit
    public int $limit = 10;
    
    // Display options
    public string $title = '';
    public string $sliderId = '';
    public string $cardVariant = 'default';
    
    public function mount(
        bool $vipOnly = false,
        bool $verifiedOnly = false,
        bool $hasVideoOnly = false,
        bool $pornActressOnly = false,
        ?string $city = null,
        ?int $ageMin = null,
        ?int $ageMax = null,
        ?string $ageGroup = null,
        string $sortBy = 'created_at',
        string $sortDirection = 'desc',
        int $limit = 10,
        string $title = '',
        ?string $sliderId = null,
        string $cardVariant = 'default'
    ) {
        $this->vipOnly = $vipOnly;
        $this->verifiedOnly = $verifiedOnly;
        $this->hasVideoOnly = $hasVideoOnly;
        $this->pornActressOnly = $pornActressOnly;
        $this->city = $city;
        $this->ageMin = $ageMin;
        $this->ageMax = $ageMax;
        $this->ageGroup = $ageGroup;
        $this->sortBy = $sortBy;
        $this->sortDirection = $sortDirection;
        $this->limit = $limit;
        $this->title = $title;
        $this->sliderId = $sliderId ?? 'profile-slider-' . uniqid();
        $this->cardVariant = $cardVariant;
    }

    #[Computed]
    public function profiles()
    {
        // `segments` is eager-loaded because x-profile-card calls allSegments()
        // on every row — without it each card issues its own pivot query.
        $query = Profile::with(['user:id,name,last_activity', 'media', 'segments'])
            ->approved()
            ->public()
            ->select($this->getPublicProfileColumns());

        // Apply filters
        if ($this->vipOnly) {
            $query->vip();
        }

        if ($this->verifiedOnly) {
            $query->verified();
        }

        if ($this->hasVideoOnly) {
            $query->whereHas('media', function($q) {
                $q->where('collection_name', 'profile-video');
            });
        }

        if ($this->pornActressOnly) {
            $query->where('is_porn_actress', true);
        }

        if ($this->city) {
            $query->where('city', 'like', '%' . $this->city . '%');
        }

        if ($this->ageMin) {
            $query->where('age', '>=', $this->ageMin);
        }

        if ($this->ageMax) {
            $query->where('age', '<=', $this->ageMax);
        }

        if ($this->ageGroup) {
            $this->applyAgeGroupFilter($query, $this->ageGroup);
        }

        $this->applySorting($query);

        // No cascading fallbacks. A slider headed "top rated this month" used to
        // silently fall back to best-rated-ever, and then to newest-first, so it
        // was never empty — it just showed profiles that did not match its own
        // heading. An empty slider renders its empty state instead.
        return $query->limit($this->limit)->get();
    }

    /**
     * Apply age group filter logic
     */
    private function applyAgeGroupFilter($query, string $ageGroup): void
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

    /**
     * Apply sorting to the query
     */
    private function applySorting($query): void
    {
        switch ($this->sortBy) {
            case 'rating':
                // Best rated profiles (all time)
                $query->withAvg('ratings', 'percentage')
                      ->whereHas('ratings')
                      ->orderBy('ratings_avg_percentage', $this->sortDirection);
                break;
            case 'rating_this_month':
                // Best rated profiles this month (based on ratings created this month)
                $query->withAvg(['ratings' => function($q) {
                          $q->where('created_at', '>=', now()->startOfMonth());
                      }], 'percentage')
                      ->whereHas('ratings', function($q) {
                          $q->where('created_at', '>=', now()->startOfMonth());
                      })
                      ->orderBy('ratings_avg_percentage', $this->sortDirection);
                break;
            case 'recommendation':
                // Sort by: 1) VIP status, 2) average rating, 3) newest
                $query->withAvg('ratings', 'percentage')
                      ->withExists('activeSubscription as is_vip')
                      ->orderBy('is_vip', $this->sortDirection)
                      ->orderBy('ratings_avg_percentage', $this->sortDirection)
                      ->orderBy('created_at', $this->sortDirection === 'desc' ? 'asc' : 'desc');
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $this->sortDirection);
                break;
        }
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
            // Needed by x-profile-card for card_height_cm / card_location.
            'content',
            'verified_at',
            'status',
            'is_porn_actress',
            'created_at',
            'updated_at'
        ];
    }

    public function render()
    {
        return view('livewire.profile-slider');
    }
}
