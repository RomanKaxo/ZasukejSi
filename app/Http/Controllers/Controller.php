<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class Controller
{
    /**
     * Get public profiles for the home page
     */
    public function index(Request $request): View
    {
        $profiles = $this->getPublicProfiles($request);
        $cities = $this->getAvailableCities();
        
        return view('profiles.index', compact('profiles', 'cities'));
    }

    /**
     * API endpoint for fetching profiles (for AJAX/Alpine.js)
     */
    public function getProfiles(Request $request): JsonResponse
    {
        $profiles = $this->getPublicProfiles($request, true);
        
        return response()->json([
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
     * Get public profiles with filters
     */
    private function getPublicProfiles(Request $request, bool $forApi = false): LengthAwarePaginator
    {
        $query = Profile::query()
            ->with(['user:id,name']) // Only load necessary user data
            ->public() // Only public profiles
            ->approved() // Only approved profiles
            ->select($this->getPublicProfileColumns()) // Limited columns for public view
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
    private function getAvailableCities(): \Illuminate\Support\Collection
    {
        return Profile::public()
            ->approved()
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
            'gender',
            'age',
            'city',
            'about',
            'verified_at',
            'status',
            'created_at',
            'updated_at'
            // Exclude sensitive data like address, availability_hours, etc.
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
            'gender' => $profile->gender,
            'age' => $profile->age,
            'city' => $profile->city,
            'about' => $profile->getTranslation('about', $currentLocale) 
                ?: $profile->getTranslation('about', 'en'),
            'is_verified' => $profile->isVerified(),
            'created_at' => $profile->created_at->format('Y-m-d'),
            'profile_url' => route('profiles.show', $profile), // Add this route later
        ];
    }
}
