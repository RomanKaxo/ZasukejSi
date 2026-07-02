<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class SubscriptionType extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'name',
        'slug',
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

    // Relationships
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->where('status', 'active');
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
