<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'subscription_type_id',
        'starts_at',
        'ends_at',
        'status',
        'cancelled_at',
        'auto_renew',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'auto_renew' => 'boolean',
            'metadata' => 'array',
        ];
    }

    // Status constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_PENDING = 'pending';

    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE => __('subscriptions.status.active'),
            self::STATUS_EXPIRED => __('subscriptions.status.expired'),
            self::STATUS_CANCELLED => __('subscriptions.status.cancelled'),
            self::STATUS_PENDING => __('subscriptions.status.pending'),
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('ends_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', self::STATUS_EXPIRED)
                ->orWhere(function ($q2) {
                    $q2->where('status', self::STATUS_ACTIVE)
                        ->where('ends_at', '<=', now());
                });
        });
    }

    public function scopeExpiring($query, int $days = 7)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereBetween('ends_at', [now(), now()->addDays($days)]);
    }

    public function scopeForProfile($query, $profileId)
    {
        return $query->where('profile_id', $profileId);
    }

    // Relationships
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function subscriptionType(): BelongsTo
    {
        return $this->belongsTo(SubscriptionType::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SubscriptionLog::class)->latest();
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->ends_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || $this->ends_at->isPast();
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->ends_at->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($this->ends_at, false);
    }

    public function getIsExpiringAttribute(): bool
    {
        return $this->isActive() && $this->days_remaining <= 7;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => $this->is_expiring ? 'warning' : 'success',
            self::STATUS_EXPIRED => 'danger',
            self::STATUS_CANCELLED => 'gray',
            self::STATUS_PENDING => 'info',
            default => 'gray',
        };
    }

    // Actions
    public function renew(?int $days = null): self
    {
        $days = $days ?? $this->subscriptionType->duration_days;
        $baseDate = $this->ends_at->isFuture() ? $this->ends_at : now();

        $this->update([
            'ends_at' => $baseDate->addDays($days),
            'status' => self::STATUS_ACTIVE,
        ]);

        $this->logAction('renewed', [
            'days_added' => $days,
            'new_end_date' => $this->ends_at->toDateTimeString(),
        ]);

        return $this;
    }

    public function cancel(?string $reason = null): self
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        $this->logAction('cancelled', [
            'reason' => $reason,
            'cancelled_at' => now()->toDateTimeString(),
        ]);

        return $this;
    }

    public function expire(): self
    {
        $this->update([
            'status' => self::STATUS_EXPIRED,
        ]);

        $this->logAction('expired');

        return $this;
    }

    public function logAction(string $action, array $metadata = []): SubscriptionLog
    {
        return $this->logs()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'metadata' => $metadata,
        ]);
    }

    // Boot
    protected static function booted(): void
    {
        static::created(function (Subscription $subscription) {
            $subscription->logAction('created', [
                'subscription_type' => $subscription->subscriptionType->name,
                'starts_at' => $subscription->starts_at->toDateTimeString(),
                'ends_at' => $subscription->ends_at->toDateTimeString(),
            ]);

            // Notify profile owner about new subscription
            if ($subscription->profile) {
                Notification::createForUser(
                    $subscription->profile->user_id,
                    __('notifications.subscription.created_title'),
                    __('notifications.subscription.created_message', [
                        'type' => $subscription->subscriptionType->name,
                        'ends_at' => $subscription->ends_at->format('d.m.Y'),
                    ]),
                    'success'
                );
            }
        });

        static::updating(function (Subscription $subscription) {
            $originalStatus = $subscription->getOriginal('status');
            $newStatus = $subscription->status;

            // Auto-expire if end date has passed
            if ($subscription->status === self::STATUS_ACTIVE && $subscription->ends_at->isPast()) {
                $subscription->status = self::STATUS_EXPIRED;
                $newStatus = self::STATUS_EXPIRED;
            }

            // Notify on status changes
            if ($originalStatus !== $newStatus && $subscription->profile) {
                $userId = $subscription->profile->user_id;

                if ($newStatus === self::STATUS_EXPIRED) {
                    Notification::createForUser(
                        $userId,
                        __('notifications.subscription.expired_title'),
                        __('notifications.subscription.expired_message'),
                        'danger'
                    );
                } elseif ($newStatus === self::STATUS_CANCELLED) {
                    Notification::createForUser(
                        $userId,
                        __('notifications.subscription.cancelled_title'),
                        __('notifications.subscription.cancelled_message'),
                        'warning'
                    );
                } elseif ($newStatus === self::STATUS_ACTIVE && $originalStatus !== self::STATUS_ACTIVE) {
                    // Renewed or reactivated
                    Notification::createForUser(
                        $userId,
                        __('notifications.subscription.renewed_title'),
                        __('notifications.subscription.renewed_message', [
                            'ends_at' => $subscription->ends_at->format('d.m.Y'),
                        ]),
                        'success'
                    );
                }
            }
        });
    }
}
