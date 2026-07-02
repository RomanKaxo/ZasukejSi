<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'user_id',
        'action',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    // Action constants
    public const ACTION_CREATED = 'created';
    public const ACTION_RENEWED = 'renewed';
    public const ACTION_CANCELLED = 'cancelled';
    public const ACTION_EXPIRED = 'expired';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_REACTIVATED = 'reactivated';

    public static function actions(): array
    {
        return [
            self::ACTION_CREATED => __('subscriptions.log.created'),
            self::ACTION_RENEWED => __('subscriptions.log.renewed'),
            self::ACTION_CANCELLED => __('subscriptions.log.cancelled'),
            self::ACTION_EXPIRED => __('subscriptions.log.expired'),
            self::ACTION_UPDATED => __('subscriptions.log.updated'),
            self::ACTION_REACTIVATED => __('subscriptions.log.reactivated'),
        ];
    }

    // Relationships
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helpers
    public function getActionLabelAttribute(): string
    {
        return self::actions()[$this->action] ?? ucfirst($this->action);
    }

    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'success',
            self::ACTION_RENEWED => 'info',
            self::ACTION_CANCELLED => 'danger',
            self::ACTION_EXPIRED => 'warning',
            self::ACTION_UPDATED => 'gray',
            self::ACTION_REACTIVATED => 'success',
            default => 'gray',
        };
    }

    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'heroicon-o-plus-circle',
            self::ACTION_RENEWED => 'heroicon-o-arrow-path',
            self::ACTION_CANCELLED => 'heroicon-o-x-circle',
            self::ACTION_EXPIRED => 'heroicon-o-clock',
            self::ACTION_UPDATED => 'heroicon-o-pencil',
            self::ACTION_REACTIVATED => 'heroicon-o-check-circle',
            default => 'heroicon-o-information-circle',
        };
    }
}
