<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-user read/archived state for a global notification.
 *
 * Global notifications are a single shared row, so their own `read_at` /
 * `archived_at` columns cannot represent per-user state. This model holds that
 * state instead, which is what stops one user from archiving or deleting a
 * global notification for everybody else.
 */
class NotificationUserState extends Model
{
    protected $table = 'notification_user_states';

    protected $fillable = [
        'notification_id',
        'user_id',
        'read_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
