<?php

namespace App\Livewire;

use App\Models\Notification;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class FavoriteButton extends Component
{
    public Profile $profile;
    public bool $isFavorited = false;
    public string $message = '';

    public function mount(Profile $profile)
    {
        $this->profile = $profile;
        
        if (Auth::check()) {
            $this->isFavorited = Auth::user()->hasFavorited($profile);
        }
    }

    public function toggleFavorite()
    {
        if (!Auth::check()) {
            $this->message = __('front.favorites.login_required');
            return;
        }

        $user = Auth::user();

        // Only male users can favorite profiles
        if (!$user->isMale()) {
            $this->message = __('front.favorites.members_only');
            return;
        }

        // Toggling fires a notification to the profile owner on every "add" —
        // throttle to stop rapid add/remove loops from spamming them.
        $throttleKey = 'toggle-favorite|' . $user->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 20)) {
            $this->message = __('front.favorites.error');
            return;
        }
        RateLimiter::hit($throttleKey, 60);

        try {
            $this->isFavorited = $user->toggleFavorite($this->profile);

            if ($this->isFavorited) {
                $this->message = __('front.favorites.added');

                // Notify profile owner
                if ($this->profile->user_id) {
                    Notification::createForUser(
                        $this->profile->user_id,
                        __('notifications.favorite.added_title'),
                        __('notifications.favorite.added_message'),
                        'info'
                    );
                }
            } else {
                $this->message = __('front.favorites.removed');
            }
        } catch (\Throwable $e) {
            Log::error('Failed to toggle favorite', [
                'profile_id' => $this->profile->id ?? null,
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);

            $this->message = __('front.favorites.error');
        }
    }

    public function render()
    {
        return view('livewire.favorite-button');
    }
}
