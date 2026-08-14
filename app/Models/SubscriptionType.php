<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class SubscriptionType extends Model
{
    use HasFactory, HasTranslations;

    /**
     * Who a plan is sold to. Profile plans are VIP tiers bought by a provider
     * for her listing; member plans are the Premium membership a male member
     * buys to unlock ratings.
     */
    public const AUDIENCE_PROFILE = 'profile';
    public const AUDIENCE_MEMBER = 'member';

    protected $fillable = [
        'name',
        'slug',
        'audience',
        'description',
        'features',
        'price',
        'duration_days',
        'color',
        'icon',
        'sort_order',
        'is_active',
    ];

    public array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price' => 'decimal:2',
            'duration_days' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * VIP tiers sold to providers. Everything that existed before the
     * `audience` column was added is one of these.
     */
    public function scopeForProfiles($query)
    {
        return $query->where('audience', self::AUDIENCE_PROFILE);
    }

    /**
     * Premium membership sold to members.
     */
    public function scopeForMembers($query)
    {
        return $query->where('audience', self::AUDIENCE_MEMBER);
    }

    public function isForMembers(): bool
    {
        return $this->audience === self::AUDIENCE_MEMBER;
    }

    /**
     * @return array<string, string>
     */
    public static function audiences(): array
    {
        return [
            self::AUDIENCE_PROFILE => __('subscriptions.audience.profile'),
            self::AUDIENCE_MEMBER => __('subscriptions.audience.member'),
        ];
    }

    // Relationships
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->where('status', 'active');
    }

    public function memberSubscriptions(): HasMany
    {
        return $this->hasMany(MemberSubscription::class);
    }

    // Helpers
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format((float) $this->price, 2);
    }

    public function getDurationLabelAttribute(): string
    {
        if ($this->duration_days === 30) {
            return __('subscriptions.duration.monthly');
        } elseif ($this->duration_days === 365) {
            return __('subscriptions.duration.yearly');
        } elseif ($this->duration_days === 7) {
            return __('subscriptions.duration.weekly');
        }

        return trans_choice('subscriptions.duration.days', $this->duration_days, ['count' => $this->duration_days]);
    }
}
