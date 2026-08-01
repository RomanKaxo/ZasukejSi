<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'is_global',
        'read_at',
        'archived_at',
    ];

    protected $casts = [
        'is_global' => 'boolean',
        'read_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Per-user read/archived state, used only for global notifications.
     */
    public function userStates()
    {
        return $this->hasMany(NotificationUserState::class);
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function archive()
    {
        $this->update(['archived_at' => now()]);
    }

    /**
     * Mark a GLOBAL notification as read for one user only.
     */
    public function markAsReadForUser($userId): void
    {
        NotificationUserState::updateOrCreate(
            ['notification_id' => $this->id, 'user_id' => $userId],
            ['read_at' => now()]
        );
    }

    /**
     * Archive a GLOBAL notification for one user only, leaving the shared row
     * (and therefore every other user's copy) untouched.
     */
    public function archiveForUser($userId): void
    {
        NotificationUserState::updateOrCreate(
            ['notification_id' => $this->id, 'user_id' => $userId],
            ['archived_at' => now(), 'read_at' => now()]
        );
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere('is_global', true);
        });
    }

    /**
     * Notifications currently visible in a user's feed: their own un-archived
     * ones plus global ones they have not archived for themselves.
     */
    public function scopeActiveForUser($query, $userId)
    {
        return $query->forUser($userId)
            ->whereNull('archived_at')
            ->whereDoesntHave('userStates', function ($q) use ($userId) {
                $q->where('user_id', $userId)->whereNotNull('archived_at');
            });
    }

    /**
     * Notifications a user has archived: their own archived ones plus global
     * ones they archived for themselves.
     */
    public function scopeArchivedForUser($query, $userId)
    {
        return $query->forUser($userId)
            ->where(function ($q) use ($userId) {
                $q->whereNotNull('archived_at')
                  ->orWhereHas('userStates', function ($inner) use ($userId) {
                      $inner->where('user_id', $userId)->whereNotNull('archived_at');
                  });
            });
    }

    /**
     * Whether this notification counts as read for the given user.
     */
    public function isReadBy($userId): bool
    {
        if (! $this->is_global) {
            return $this->read_at !== null;
        }

        return $this->userStates
            ->where('user_id', $userId)
            ->whereNotNull('read_at')
            ->isNotEmpty();
    }

    public static function createGlobal($title, $message, $type = 'info')
    {
        return static::create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_global' => true,
        ]);
    }

    public static function createForUser($userId, $title, $message, $type = 'info')
    {
        return static::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_global' => false,
        ]);
    }
}
