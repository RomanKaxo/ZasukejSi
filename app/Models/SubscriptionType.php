<?php

namespace App\Models;

use App\Support\Currencies;
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
        'price_czk',
        'price_eur',
        'price_usd',
        'duration_days',
        'color',
        'icon',
        'sort_order',
        'is_active',
        'show_on_plans_page',
    ];

    public array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price' => 'decimal:2',
            'price_czk' => 'decimal:2',
            'price_eur' => 'decimal:2',
            'price_usd' => 'decimal:2',
            'duration_days' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'show_on_plans_page' => 'boolean',
        ];
    }

    /**
     * The amount in a given currency, falling back to the legacy `price`
     * column so a plan priced before the per-currency columns existed still
     * quotes something rather than nothing.
     */
    public function priceIn(?string $currency = null): ?float
    {
        $currency = strtoupper($currency ?? Currencies::forLocale());

        $value = match ($currency) {
            Currencies::CZK => $this->price_czk,
            Currencies::EUR => $this->price_eur,
            Currencies::USD => $this->price_usd,
            default => null,
        };

        if ($value !== null) {
            return (float) $value;
        }

        // Only fall back for the currency the plan was originally entered in;
        // showing a koruna amount labelled in euros would misquote it.
        $legacyCurrency = $this->audience === self::AUDIENCE_MEMBER
            ? Currencies::CZK
            : Currencies::EUR;

        return $currency === $legacyCurrency && $this->price !== null
            ? (float) $this->price
            : null;
    }

    /** Ready-to-print price, e.g. "299 Kč" or "€24.99". */
    public function formattedPrice(?string $currency = null): ?string
    {
        $currency = strtoupper($currency ?? Currencies::forLocale());
        $amount = $this->priceIn($currency);

        return $amount === null ? null : Currencies::format($amount, $currency);
    }

    /** "30 dní" / "3 měsíce" / "1 rok", derived from duration_days. */
    public function periodLabel(): string
    {
        $days = (int) $this->duration_days;

        return match (true) {
            $days % 365 === 0 && $days >= 365 => trans_choice('front.membership.period_years', intdiv($days, 365), ['count' => intdiv($days, 365)]),
            $days % 30 === 0 && $days >= 60 => trans_choice('front.membership.period_months', intdiv($days, 30), ['count' => intdiv($days, 30)]),
            default => trans_choice('front.membership.period_days', $days, ['count' => $days]),
        };
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Plans advertised on the public VIP & Premium page.
     *
     * Different question from `is_active`: an inactive plan cannot be bought
     * at all, this one is merely not on the shop window. A tier being phased
     * out is still renewed from inside an account.
     */
    public function scopeOnPlansPage($query)
    {
        return $query->where('show_on_plans_page', true);
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
