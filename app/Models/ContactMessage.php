<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A message sent through the contact page.
 *
 * A signed-in sender is linked to their account — and, if they have one, to
 * their profile — so an admin can see who is writing without having to match
 * on the e-mail address they happened to type.
 */
class ContactMessage extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'message',
        'user_id',
        'profile_id',
        'status',
        'read_at',
        'admin_note',
        'locale',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEW => __('contact.status.new'),
            self::STATUS_IN_PROGRESS => __('contact.status.in_progress'),
            self::STATUS_RESOLVED => __('contact.status.resolved'),
        ];
    }
}
