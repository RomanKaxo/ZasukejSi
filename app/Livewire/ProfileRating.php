<?php

namespace App\Livewire;

use App\Models\Notification;
use App\Models\Profile;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
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
        if (!Auth::check()) {
            $this->message = __('front.profiles.rating.login_required');
            return;
        }

        if ($rating < 1 || $rating > 5) {
            $this->message = __('front.profiles.rating.invalid_rating');
            return;
        }

        try {
            $existingRating = Rating::where('profile_id', $this->profile->id)
                ->where('user_id', Auth::id())
                ->first();
            
            $isNewRating = !$existingRating;

            Rating::updateOrCreate(
                [
                    'profile_id' => $this->profile->id,
                    'user_id' => Auth::id(),
                ],
                [
                    'rating' => $rating,
                ]
            );

            $this->userRating = $rating;
            $this->currentRating = $rating;
            
            // Refresh the ratings
            $this->profile->refresh();
            $this->averageRating = $this->profile->getAverageRating();
            $this->totalRatings = $this->profile->getTotalRatings();

            // Notify profile owner about new rating (not updates)
            if ($isNewRating && $this->profile->user_id !== Auth::id()) {
                Notification::createForUser(
                    $this->profile->user_id,
                    __('notifications.rating.received_title'),
                    __('notifications.rating.received_message', ['stars' => $rating]),
                    $rating >= 4 ? 'success' : ($rating >= 3 ? 'info' : 'warning')
                );
            }
            
            $this->message = __('front.profiles.rating.success');
        } catch (\Exception $e) {
            $this->message = __('front.profiles.rating.error');
        }
    }

    public function render()
    {
        return view('livewire.profile-rating');
    }
}
