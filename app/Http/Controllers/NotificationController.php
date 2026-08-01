<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

    public function archive(Notification $notification)
    {
        $this->authorizeOwnership($notification);

        // A global notification is shared by every user, so archiving it must be
        // recorded per-user instead of mutating the shared row.
        if ($notification->is_global) {
            $notification->archiveForUser(Auth::id());
        } else {
            $notification->archive();
        }

        return back()->with('success', __('front.notifications.notification_archived'));
    }

    public function delete(Notification $notification)
    {
        $this->authorizeOwnership($notification);

        // Global notifications may only be removed from the current user's feed —
        // deleting the row would remove it for everyone on the platform.
        if ($notification->is_global) {
            $notification->archiveForUser(Auth::id());
        } else {
            $notification->delete();
        }

        return back()->with('success', __('front.notifications.notification_deleted'));
    }

    public function markAsRead(Notification $notification)
    {
        $this->authorizeOwnership($notification);

        if ($notification->is_global) {
            $notification->markAsReadForUser(Auth::id());
        } else {
            $notification->markAsRead();
        }

        return back();
    }

    /**
     * A user may only act on their own notifications, or on global ones
     * (which are then only ever mutated through the per-user pivot).
     */
    private function authorizeOwnership(Notification $notification): void
    {
        abort_unless(
            $notification->user_id === Auth::id() || $notification->is_global,
            403
        );
    }

    public function archived()
    {
        $notifications = Notification::archivedForUser(Auth::id())
            ->with('userStates')
            ->latest()
            ->paginate(20);

        return view('account.notifications.archived', compact('notifications'));
    }
}
