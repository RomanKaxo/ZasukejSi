<?php

namespace App\Livewire;

use App\Models\Notification;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
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
        } catch (\Exception $e) {
            $this->message = __('front.favorites.error');
        }
    }

    public function render()
    {
        return view('livewire.favorite-button');
    }
}
