<?php

namespace App\Livewire;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationsDropdown extends Component
{
    public bool $isOpen = false;

    public function archive(int $notificationId)
    {
        $notification = Notification::find($notificationId);
        
        if ($notification && ($notification->user_id === Auth::id() || $notification->is_global)) {
            $notification->archive();
        }
    }

    public function getUnreadCountProperty(): int
    {
        return Notification::forUser(Auth::id())
            ->active()
            ->unread()
            ->count();
    }

    public function getNotificationsProperty()
    {
        return Notification::forUser(Auth::id())
            ->active()
            ->latest()
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.notifications-dropdown');
    }
}
