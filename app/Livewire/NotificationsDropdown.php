<?php

namespace App\Livewire;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationsDropdown extends Component
{
    public bool $isOpen = false;

    /**
     * Archive a notification for the current user.
     *
     * Global notifications are shared rows, so they are archived through the
     * per-user state pivot — otherwise one user would hide it for everyone.
     */
    public function archive(int $notificationId)
    {
        $notification = Notification::find($notificationId);

        if (! $notification) {
            return;
        }

        if ($notification->is_global) {
            $notification->archiveForUser(Auth::id());
            return;
        }

        if ($notification->user_id === Auth::id()) {
            $notification->archive();
        }
    }

    /**
     * Mark a single notification as read for the current user.
     */
    public function markAsRead(int $notificationId)
    {
        $notification = Notification::find($notificationId);

        if (! $notification) {
            return;
        }

        if ($notification->is_global) {
            $notification->markAsReadForUser(Auth::id());
            return;
        }

        if ($notification->user_id === Auth::id()) {
            $notification->markAsRead();
        }
    }

    /**
     * Mark every notification currently visible to the user as read.
     */
    public function markAllAsRead()
    {
        $userId = Auth::id();

        Notification::activeForUser($userId)
            ->with('userStates')
            ->get()
            ->each(function (Notification $notification) use ($userId) {
                if ($notification->is_global) {
                    if (! $notification->isReadBy($userId)) {
                        $notification->markAsReadForUser($userId);
                    }
                } elseif ($notification->read_at === null) {
                    $notification->markAsRead();
                }
            });
    }

    public function getUnreadCountProperty(): int
    {
        $userId = Auth::id();

        return Notification::activeForUser($userId)
            ->where(function ($query) use ($userId) {
                // Personal notifications are unread while read_at is null.
                $query->where(function ($q) {
                    $q->where('is_global', false)->whereNull('read_at');
                })
                // Global notifications are unread until this user has a read state.
                ->orWhere(function ($q) use ($userId) {
                    $q->where('is_global', true)
                      ->whereDoesntHave('userStates', function ($inner) use ($userId) {
                          $inner->where('user_id', $userId)->whereNotNull('read_at');
                      });
                });
            })
            ->count();
    }

    public function getNotificationsProperty()
    {
        return Notification::activeForUser(Auth::id())
            ->with('userStates')
            ->latest()
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.notifications-dropdown');
    }
}
