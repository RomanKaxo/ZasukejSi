<?php

namespace App\Livewire;

use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ProfileRating extends Component
{
    public Profile $profile;
    public $currentRating = 0;
    public $userRating = null;
    public $averageRating = 0;
    public $totalRatings = 0;
    public $message = '';

    public function mount(Profile $profile)
    {
        $this->profile = $profile;
        $this->averageRating = $profile->getAverageRating();
        $this->totalRatings = $profile->getTotalRatings();
        
        if (Auth::check()) {
            $this->userRating = $profile->getUserRating(Auth::id());
            $this->currentRating = $this->userRating ?? 0;
        }
    }

    public function rate($rating)
    {
        // Cast defensively: the value arrives from the browser and must be a
        // real integer before it reaches the unsignedTinyInteger column.
        if (!is_numeric($rating)) {
            $this->message = __('front.profiles.rating.invalid_rating');
            return;
        }

        try {
            // Shared implementation on the model — same authorization rules as
            // the member ratings page.
            $result = $this->profile->rateBy(Auth::user(), (int) $rating);

            $this->message = match ($result) {
                Profile::RATE_OK => __('front.profiles.rating.success'),
                Profile::RATE_NOT_LOGGED_IN => __('front.profiles.rating.login_required'),
                Profile::RATE_NOT_MEMBER => __('front.profiles.rating.members_only'),
                Profile::RATE_OWN_PROFILE => __('front.profiles.rating.own_profile'),
                default => __('front.profiles.rating.invalid_rating'),
            };

            if ($result !== Profile::RATE_OK) {
                return;
            }

            $this->userRating = (int) $rating;
            $this->currentRating = (int) $rating;

            // Refresh the aggregate figures
            $this->profile->refresh();
            $this->averageRating = $this->profile->getAverageRating();
            $this->totalRatings = $this->profile->getTotalRatings();
        } catch (\Throwable $e) {
            Log::error('Failed to rate profile', [
                'profile_id' => $this->profile->id ?? null,
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);

            $this->message = __('front.profiles.rating.error');
        }
    }

    public function render()
    {
        return view('livewire.profile-rating');
    }
}
