<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
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
        // Check if user owns this notification or if it's a global notification
        if ($notification->user_id === Auth::id() || $notification->is_global) {
            $notification->archive();
            return back()->with('success', __('front.notifications.notification_archived'));
        }

        abort(403);
    }

    public function delete(Notification $notification)
    {
        // Check if user owns this notification or if it's a global notification
        if ($notification->user_id === Auth::id() || $notification->is_global) {
            $notification->delete();
            return back()->with('success', __('front.notifications.notification_deleted'));
        }

        abort(403);
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id === Auth::id() || $notification->is_global) {
            $notification->markAsRead();
            return back();
        }

        abort(403);
    }

    public function archived()
    {
        $notifications = Notification::forUser(Auth::id())
            ->archived()
            ->latest()
            ->paginate(20);

        return view('account.notifications.archived', compact('notifications'));
    }
}
