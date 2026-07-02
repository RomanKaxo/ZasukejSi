<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Profile;
use App\Models\ProfileView;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display a listing of public profiles.
     */
    public function index(Request $request): View
    {
        $profiles = $this->getPublicProfiles($request);
        
        // Record impressions for profiles shown in listing (don't track for authenticated female users viewing their own)
        $this->recordListingImpressions($profiles);
        
        // Get published blog posts
        $blogPosts = Page::blog()
            ->published()
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        // Get user counts by gender
        $girlsCount = User::where('gender', 'female')->count();
        $gentsCount = User::where('gender', 'male')->count();

        return view('profiles.index', compact('profiles', 'blogPosts', 'girlsCount', 'gentsCount'));
    }

    /**
     * API endpoint for fetching profiles (AJAX/Alpine.js)
     */
    public function api(Request $request): JsonResponse
    {
        $profiles = $this->getPublicProfiles($request, true);
        
        // Record impressions for profiles shown in API response
        $this->recordListingImpressions($profiles);
        
        return response()->json([
            'success' => true,
            'data' => $profiles->items(),
            'pagination' => [
                'current_page' => $profiles->currentPage(),
                'last_page' => $profiles->lastPage(),
                'per_page' => $profiles->perPage(),
                'total' => $profiles->total(),
                'has_more' => $profiles->hasMorePages(),
            ],
            'filters' => [
                'cities' => $this->getAvailableCities(),
                'current' => $request->only(['city', 'age_min', 'age_max', 'verified']),
            ]
        ]);
    }

    /**
     * Show individual profile
     */
    public function show($id): View
    {
        $profile = Profile::public()
            ->approved()
            ->with(['user:id,name', 'services'])
            ->select($this->getPublicProfileColumns())
            ->findOrFail($id);
        
        // Record profile click view (don't track own profile views)
        if (!auth()->check() || auth()->id() !== $profile->user_id) {
            ProfileView::recordClick($profile->id);
        }
            
        return view('profiles.show', compact('profile'));
    }

    /**
     * Get public profiles with filters
     */
    private function getPublicProfiles(Request $request, bool $forApi = false)
    {
        $query = Profile::with('user:id,name')
            ->approved()
            ->public()
            ->select($this->getPublicProfileColumns())
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('age_min')) {
            $query->where('age', '>=', $request->age_min);
        }

        if ($request->filled('age_max')) {
            $query->where('age', '<=', $request->age_max);
        }

        if ($request->boolean('verified')) {
            $query->verified();
        }

        // Pagination
        $perPage = $forApi ? ($request->get('per_page', 10)) : 10;
        $profiles = $query->paginate($perPage);

        // Transform data for API responses
        if ($forApi) {
            $profiles->getCollection()->transform(function ($profile) {
                return $this->transformProfileForApi($profile);
            });
        }

        return $profiles;
    }

    /**
     * Get available cities for filtering
     */
    private function getAvailableCities()
    {
        return Profile::approved()
            ->public()
            ->whereNotNull('city')
            ->pluck('city')
            ->unique()
            ->sort()
            ->values();
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
            'incall',
            'outcall',
            'local_prices',
            'verified_at',
            'status',
            'created_at',
            'updated_at'
        ];
    }

    /**
     * Transform profile data for API response
     */
    private function transformProfileForApi(Profile $profile): array
    {
        $currentLocale = app()->getLocale();
        
        return [
            'id' => $profile->id,
            'display_name' => $profile->getTranslation('display_name', $currentLocale) 
                ?: $profile->getTranslation('display_name', 'en')
                ?: __('Anonymous Therapist'),
            'age' => $profile->age,
            'city' => $profile->city,
            'about' => $profile->getTranslation('about', $currentLocale) 
                ?: $profile->getTranslation('about', 'en'),
            'is_verified' => $profile->isVerified(),
            'created_at' => $profile->created_at->format('Y-m-d'),
            'profile_url' => route('profiles.show', $profile),
        ];
    }

    /**
     * Record impressions for profiles shown in a listing.
     * Excludes own profile for authenticated female users.
     */
    private function recordListingImpressions($profiles): void
    {
        $profileIds = collect($profiles->items())->pluck('id')->toArray();
        
        // If user is authenticated and female, exclude their own profile
        if (auth()->check() && auth()->user()->isFemale() && auth()->user()->profile) {
            $ownProfileId = auth()->user()->profile->id;
            $profileIds = array_filter($profileIds, fn($id) => $id !== $ownProfileId);
        }
        
        if (!empty($profileIds)) {
            ProfileView::recordImpressions($profileIds);
        }
    }
}
