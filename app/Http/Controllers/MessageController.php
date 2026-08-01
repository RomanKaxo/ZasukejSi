<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function inbox()
    {
        $userId = Auth::id();

        // Get all conversations (unique users we've messaged with).
        // The user id is bound as a parameter rather than interpolated into the
        // raw expression.
        $conversations = DB::table('messages')
            ->select(
                DB::raw('CASE WHEN from_user_id = ? THEN to_user_id ELSE from_user_id END as other_user_id'),
                DB::raw('MAX(created_at) as last_message_at'),
                DB::raw('SUM(CASE WHEN to_user_id = ? AND read_at IS NULL THEN 1 ELSE 0 END) as unread_count')
            )
            ->addBinding([$userId, $userId], 'select')
            ->where(function($query) use ($userId) {
                $query->where('from_user_id', $userId)
                      ->orWhere('to_user_id', $userId);
            })
            ->whereNull('deleted_at')
            ->groupBy('other_user_id')
            ->orderBy('last_message_at', 'desc')
            ->get();

        $users = User::whereIn('id', $conversations->pluck('other_user_id'))
            ->get()
            ->keyBy('id');

        return view('messages.index', compact('conversations', 'users'));
    }

    public function show(User $user)
    {
        $messages = Message::betweenUsers(Auth::id(), $user->id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        Message::where('to_user_id', Auth::id())
            ->where('from_user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('messages.show', compact('user', 'messages'));
    }

    public function store(Request $request, User $user)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        // Messaging yourself is not a meaningful conversation and would create a
        // thread that the inbox query cannot represent.
        if ($user->id === Auth::id()) {
            return back()->withErrors(['message' => __('front.messages.cannot_message_self')]);
        }

        Message::create([
            'from_user_id' => Auth::id(),
            'to_user_id' => $user->id,
            'message' => $request->message,
        ]);

        // Let the recipient know, consistent with ratings/favorites behaviour.
        Notification::createForUser(
            $user->id,
            __('notifications.message.received_title'),
            __('notifications.message.received_message', ['name' => Auth::user()->name]),
            'info'
        );

        return back()->with('success', __('front.messages.message_sent'));
    }
}
