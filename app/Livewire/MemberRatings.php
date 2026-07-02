<?php

namespace App\Livewire;

use App\Models\Notification;
use App\Models\Profile;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Livewire component for member ratings page.
 * Allows male users to quickly rate female profiles with a split view interface.
 */
class MemberRatings extends Component
{
    public ?Profile $selectedProfile = null;
    public int $selectedProfileId = 0;
    public bool $isFavorited = false;

    protected $listeners = ['profileSelected' => 'selectProfile'];

    public function mount()
    {
        // Load the first available profile by default
        $firstProfile = $this->getAvailableProfiles()->first();
        if ($firstProfile) {
            $this->selectProfile($firstProfile->id);
        }
    }

    /**
     * Get available profiles that can be rated.
     * Must be approved, public, and belong to female users.
     */
    public function getAvailableProfiles()
    {
        return Profile::approved()
            ->public()
            ->whereHas('user', fn($q) => $q->where('gender', 'female'))
            ->with(['media'])
            ->orderBy('display_name')
            ->get();
    }

    /**
     * Select a profile to display in the main view.
     */
    public function selectProfile(int $profileId)
    {
        $this->selectedProfile = Profile::approved()
            ->public()
            ->with(['media', 'user'])
            ->find($profileId);

        if ($this->selectedProfile) {
            $this->selectedProfileId = $profileId;
            $this->updateFavoriteStatus();
        }
    }

    /**
     * Update the favorite status for the selected profile.
     */
    private function updateFavoriteStatus()
    {
        if (!$this->selectedProfile || !Auth::check()) {
            $this->isFavorited = false;
            return;
        }

        $this->isFavorited = Auth::user()->hasFavorited($this->selectedProfile);
    }

    /**
     * Toggle favorite status for the selected profile.
     */
    public function toggleFavorite()
    {
        if (!Auth::check() || !$this->selectedProfile) {
            return;
        }

        $user = Auth::user();

        // Only male users can favorite profiles
        if (!$user->isMale()) {
            return;
        }

        $this->isFavorited = $user->toggleFavorite($this->selectedProfile);

        if ($this->isFavorited && $this->selectedProfile->user_id) {
            Notification::createForUser(
                $this->selectedProfile->user_id,
                __('notifications.favorite.added_title'),
                __('notifications.favorite.added_message'),
                'info'
            );
        }
    }

    /**
     * Rate the selected profile with a percentage value.
     * Maps percentages to 1-5 star ratings:
     * - 100% = 5 stars
     * - 70% = 4 stars (rounded from 3.5)
     * - 30% = 2 stars (rounded from 1.5)
     */
    public function rateProfile(int $percentage)
    {
        if (!Auth::check() || !$this->selectedProfile) {
            return;
        }

        $user = Auth::user();

        // Only male users can rate profiles
        if (!$user->isMale()) {
            return;
        }

        // Map percentage to star rating (1-5)
        $starRating = match ($percentage) {
            100 => 5,
            70 => 4,
            30 => 2,
            default => 3,
        };

        $existingRating = Rating::where('profile_id', $this->selectedProfile->id)
            ->where('user_id', $user->id)
            ->first();

        $isNewRating = !$existingRating;

        Rating::updateOrCreate(
            [
                'profile_id' => $this->selectedProfile->id,
                'user_id' => $user->id,
            ],
            [
                'rating' => $starRating,
            ]
        );

        // Notify profile owner about new rating
        if ($isNewRating && $this->selectedProfile->user_id !== $user->id) {
            Notification::createForUser(
                $this->selectedProfile->user_id,
                __('notifications.rating.received_title'),
                __('notifications.rating.received_message', ['stars' => $starRating]),
                $starRating >= 4 ? 'success' : ($starRating >= 3 ? 'info' : 'warning')
            );
        }

        // Refresh the view to show the updated rating
        $this->updateFavoriteStatus();
    }

    /**
     * Skip the current profile and move to the next one.
     */
    public function skipProfile()
    {
        $this->moveToNextProfile();
    }

    /**
     * Move to the next unrated profile in the list.
     */
    private function moveToNextProfile()
    {
        if (!Auth::check()) {
            return;
        }

        $userId = Auth::id();
        $currentId = $this->selectedProfileId;

        // Get profiles the user hasn't rated yet, preferring ones after current
        $nextProfile = Profile::approved()
            ->public()
            ->whereHas('user', fn($q) => $q->where('gender', 'female'))
            ->whereDoesntHave('ratings', fn($q) => $q->where('user_id', $userId))
            ->where('id', '>', $currentId)
            ->with(['media'])
            ->orderBy('id')
            ->first();

        // If no profile after current, try from the beginning
        if (!$nextProfile) {
            $nextProfile = Profile::approved()
                ->public()
                ->whereHas('user', fn($q) => $q->where('gender', 'female'))
                ->whereDoesntHave('ratings', fn($q) => $q->where('user_id', $userId))
                ->where('id', '!=', $currentId)
                ->with(['media'])
                ->orderBy('id')
                ->first();
        }

        if ($nextProfile) {
            $this->selectProfile($nextProfile->id);
        }
    }

    /**
     * Get the user's existing rating for the selected profile.
     */
    public function getUserRatingForSelected(): ?int
    {
        if (!Auth::check() || !$this->selectedProfile) {
            return null;
        }

        return $this->selectedProfile->getUserRating(Auth::id());
    }

    public function render()
    {
        return view('livewire.member-ratings', [
            'profiles' => $this->getAvailableProfiles(),
            'userRating' => $this->getUserRatingForSelected(),
        ]);
    }
}
