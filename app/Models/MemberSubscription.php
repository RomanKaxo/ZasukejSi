<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A paid membership held by a member (male user).
 *
 * Deliberately mirrors App\Models\Subscription — same status constants, same
 * scope names, same renew/cancel/expire verbs — so the two read identically
 * even though they are keyed differently (`user_id` here, `profile_id` there).
 * See migration 2026_08_14_000005 for why they are separate tables.
 */
class MemberSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_type_id',
        'starts_at',
        'ends_at',
        'status',
        'cancelled_at',
        'auto_renew',
        'expiring_notified_at',
        'notes',
        'metadata',
        'payment_method',
        'payment_reference',
        'paid_at',
        'payment_confirmed_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'paid_at' => 'datetime',
            'expiring_notified_at' => 'datetime',
            'auto_renew' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_PENDING = 'pending';

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE => __('subscriptions.status.active'),
            self::STATUS_EXPIRED => __('subscriptions.status.expired'),
            self::STATUS_CANCELLED => __('subscriptions.status.cancelled'),
            self::STATUS_PENDING => __('subscriptions.status.pending'),
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('ends_at', '>', now());
    }

    public function scopeExpiring($query, int $days = 7)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereBetween('ends_at', [now(), now()->addDays($days)]);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptionType(): BelongsTo
    {
        return $this->belongsTo(SubscriptionType::class);
    }

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
        return $this->ends_at->isPast() ? 0 : (int) now()->diffInDays($this->ends_at, false);
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

    /**
     * Extend the membership. Renewing before it lapses adds to the remaining
     * time rather than throwing it away.
     */
    public function renew(?int $days = null): self
    {
        $days = $days ?? $this->subscriptionType->duration_days;
        $baseDate = $this->ends_at->isFuture() ? $this->ends_at : now();

        $this->update([
            'ends_at' => $baseDate->copy()->addDays($days),
            'status' => self::STATUS_ACTIVE,
            // New period, so the expiry warning is owed again.
            'expiring_notified_at' => null,
        ]);

        return $this;
    }

    public function cancel(?string $reason = null): self
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'notes' => $reason ?: $this->notes,
        ]);

        return $this;
    }

    public function expire(): self
    {
        $this->update(['status' => self::STATUS_EXPIRED]);

        return $this;
    }

    protected static function booted(): void
    {
        static::created(function (MemberSubscription $subscription) {
            // Objednávka zaplacená převodem ještě neběží a datum konce nemá.
            // Zpráva „členství platí do —" by byla nepravdivá; ta správná
            // přijde, až se platba potvrdí.
            if ($subscription->ends_at === null) {
                return;
            }

            Notification::createForUser(
                $subscription->user_id,
                __('notifications.membership.created_title'),
                __('notifications.membership.created_message', [
                    'type' => $subscription->subscriptionType?->name ?? '',
                    'ends_at' => $subscription->ends_at->format('d.m.Y'),
                ]),
                'success'
            );
        });

        static::updating(function (MemberSubscription $subscription) {
            $originalStatus = $subscription->getOriginal('status');
            $newStatus = $subscription->status;

            // Auto-expire once the end date has passed, matching Subscription.
            if ($newStatus === self::STATUS_ACTIVE && $subscription->ends_at->isPast()) {
                $subscription->status = self::STATUS_EXPIRED;
                $newStatus = self::STATUS_EXPIRED;
            }

            if ($originalStatus === $newStatus) {
                return;
            }

            if ($newStatus === self::STATUS_EXPIRED) {
                Notification::createForUser(
                    $subscription->user_id,
                    __('notifications.membership.expired_title'),
                    __('notifications.membership.expired_message'),
                    'danger'
                );
            } elseif ($newStatus === self::STATUS_CANCELLED) {
                Notification::createForUser(
                    $subscription->user_id,
                    __('notifications.membership.cancelled_title'),
                    __('notifications.membership.cancelled_message'),
                    'warning'
                );
            } elseif ($newStatus === self::STATUS_ACTIVE) {
                Notification::createForUser(
                    $subscription->user_id,
                    __('notifications.membership.renewed_title'),
                    __('notifications.membership.renewed_message', [
                        'ends_at' => $subscription->ends_at->format('d.m.Y'),
                    ]),
                    'success'
                );
            }
        });
    }
}
