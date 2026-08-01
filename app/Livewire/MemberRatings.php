<?php

namespace App\Livewire;

use App\Models\Notification;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Livewire component for member ratings page.
 * Allows male users to quickly rate female profiles with a split view interface.
 */
class MemberRatings extends Component
{
    use WithPagination;

    /** How many profiles the rating-history panel loads at a time. */
    public const PROFILES_PER_PAGE = 15;

    public ?Profile $selectedProfile = null;
    public int $selectedProfileId = 0;
    public bool $isFavorited = false;
    /** User-facing message shown when a rating attempt is refused. */
    public string $ratingError = '';

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
     * Base query for profiles that can be rated: approved, public and owned by
     * a female user.
     *
     * The rating aggregates are pulled in with the query (withCount/withAvg plus
     * an eager-loaded rating row for the current user) so the history list can
     * render without issuing three extra queries per profile — previously 20
     * profiles cost 60 additional queries.
     */
    public function availableProfilesQuery()
    {
        return Profile::approved()
            ->public()
            ->whereHas('user', fn($q) => $q->where('gender', 'female'))
            ->with([
                'media',
                'ratings' => fn ($q) => $q->where('user_id', Auth::id()),
            ])
            ->withCount('ratings')
            ->withAvg('ratings', 'rating')
            ->orderBy('display_name');
    }

    /**
     * Paginated list of profiles shown in the rating-history panel.
     */
    public function getAvailableProfiles()
    {
        return $this->availableProfilesQuery()->paginate(self::PROFILES_PER_PAGE);
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
        if (!$this->selectedProfile) {
            return;
        }

        // Map percentage to star rating (1-5)
        $starRating = match ($percentage) {
            100 => 5,
            70 => 4,
            30 => 2,
            default => 3,
        };

        try {
            // Shared implementation on the model — identical authorization to
            // the rating widget on the public profile detail page.
            $result = $this->selectedProfile->rateBy(Auth::user(), $starRating);

            if ($result !== Profile::RATE_OK) {
                $this->ratingError = match ($result) {
                    Profile::RATE_NOT_LOGGED_IN => __('front.profiles.rating.login_required'),
                    Profile::RATE_NOT_MEMBER => __('front.profiles.rating.members_only'),
                    Profile::RATE_OWN_PROFILE => __('front.profiles.rating.own_profile'),
                    default => __('front.profiles.rating.invalid_rating'),
                };

                return;
            }

            $this->ratingError = '';
        } catch (\Throwable $e) {
            Log::error('Failed to rate profile from member ratings page', [
                'profile_id' => $this->selectedProfile->id,
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);

            $this->ratingError = __('front.profiles.rating.error');

            return;
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
            'regions' => \App\Http\Controllers\Auth\MemberController::CZECH_REGIONS,
        ]);
    }
}
