<?php

namespace App\Models;

use App\Support\RatingScale;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class Profile extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ProfileFactory> */
    use HasFactory, SoftDeletes, HasTranslations, InteractsWithMedia;

    /**
     * The attributes that are translatable.
     *
     * @var array<int, string>
     */
    public $translatable = ['display_name', 'about'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'user_id',
        'display_name',
        'age',
        'city',
        'address',
        'country_code',
        'about',
        'incall',
        'outcall',
        // `content` is the attribute map (height, weight, languages, …) written
        // by ProfileForm; `content_blocks` is the admin's rich-content builder.
        // They live in separate columns because a single shared one made every
        // profile with attributes uneditable in Filament — see migration
        // 2026_08_14_000003_split_profile_content_blocks.
        'content',
        'content_blocks',
        'availability_hours',
        'local_prices',
        'global_prices',
        'price_currency',
        'auto_convert_prices',
        'contacts',
        'verified_at',
        'status',
        'is_public',
        'is_porn_actress',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'content_blocks' => 'array',
            'availability_hours' => 'array',
            'local_prices' => 'array',
            'global_prices' => 'array',
            'contacts' => 'array',
            'verified_at' => 'datetime',
            'is_public' => 'boolean',
            'is_porn_actress' => 'boolean',
            'auto_convert_prices' => 'boolean',
            'incall' => 'boolean',
            'outcall' => 'boolean',
        ];
    }

    /**
     * ISO-3166-1 alpha-2 codes are stored uppercase, matching `cities.country_code`.
     *
     * Every country/region feature joins these two columns, so the casing has to
     * be an invariant rather than something a case-insensitive MySQL collation
     * happens to hide. See migration 2026_08_14_000001_normalize_country_codes.
     */
    public function setCountryCodeAttribute($value): void
    {
        $this->attributes['country_code'] = $value === null || $value === ''
            ? null
            : strtoupper((string) $value);
    }

    /**
     * Physical/descriptive attributes are stored inside the `content` JSON
     * column (written by App\Livewire\ProfileForm). These accessors expose them
     * as first-class attributes so views can use `$profile->weight` instead of
     * digging into the raw array — and so a missing value fails visibly rather
     * than silently resolving to null.
     */
    protected function contentValue(string $key): mixed
    {
        $content = $this->content;

        if (! is_array($content)) {
            return null;
        }

        $value = $content[$key] ?? null;

        // ProfileForm stores empty inputs as '' or null; normalise both to null
        // so `?? $fallback` behaves as expected in the views.
        return ($value === '' || $value === null) ? null : $value;
    }

    /**
     * Body weight in kilograms.
     */
    public function getWeightAttribute(): ?int
    {
        $value = $this->contentValue('weight_kg');

        return $value === null ? null : (int) $value;
    }

    /**
     * Body height in centimetres.
     */
    public function getHeightAttribute(): ?int
    {
        $value = $this->contentValue('card_height_cm');

        return $value === null ? null : (int) $value;
    }

    /**
     * Bust size (A–H).
     */
    public function getBustSizeAttribute(): ?string
    {
        $value = $this->contentValue('bust_size');

        return $value === null ? null : (string) $value;
    }

    /**
     * ISO country code of the profile's nationality.
     */
    public function getNationalityAttribute(): ?string
    {
        $value = $this->contentValue('nationality');

        return $value === null ? null : strtolower((string) $value);
    }

    /**
     * Comma separated list of spoken languages.
     */
    public function getLanguagesAttribute(): ?string
    {
        $value = $this->contentValue('languages');

        return $value === null ? null : (string) $value;
    }

    /**
     * Whether the profile's single phone number also accepts WhatsApp.
     */
    public function getHasWhatsappAttribute(): bool
    {
        return (bool) $this->contentValue('has_whatsapp');
    }

    /**
     * Whether the profile's single phone number also accepts Telegram.
     */
    public function getHasTelegramAttribute(): bool
    {
        return (bool) $this->contentValue('has_telegram');
    }

    /**
     * The region/kraj the profile's city belongs to, looked up from the
     * `cities` table (its `admin_name` column, backfilled from
     * worldcities.csv). Not stored on the profile itself since `city` is a
     * free-text value chosen via autocomplete against that same table.
     */
    public function getRegionAttribute(): ?string
    {
        if (! $this->city) {
            return null;
        }

        return City::query()
            ->when($this->country_code, fn ($q) => $q->where('country_code', strtoupper($this->country_code)))
            ->where(function ($q) {
                $q->where('name', $this->city)->orWhere('name_ascii', $this->city);
            })
            ->value('admin_name');
    }

    /**
     * Weight converted to pounds, derived from `weight`.
     */
    public function getWeightLbsAttribute(): ?int
    {
        $weight = $this->weight;

        return $weight === null ? null : (int) round($weight * 2.20462);
    }

    /**
     * Height rendered as feet + inches (e.g. 5'6"), derived from `height`.
     */
    public function getHeightFeetAttribute(): ?string
    {
        $height = $this->height;

        if ($height === null) {
            return null;
        }

        $totalInches = (int) round($height / 2.54);
        $feet = intdiv($totalInches, 12);
        $inches = $totalInches % 12;

        return $feet . "'" . $inches . '"';
    }

    /**
     * Get the user that owns the profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the users who have favorited this profile.
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'profile_favorites')
            ->withTimestamps();
    }

    /**
     * Get the services associated with this profile.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'profile_service')
            // Each service carries its own price list, one amount per currency.
            ->withPivot(['prices', 'note'])
            ->withTimestamps();
    }

    /**
     * What this profile charges for a service, in a given currency.
     *
     * Falls back to converting from whatever currency the provider did enter,
     * but only when she asked for that — an amount she typed is never
     * replaced, and a converted one is reported as such by the caller.
     */
    public function servicePrice(Service $service, ?string $currency = null): ?float
    {
        $currency = strtoupper($currency ?? \App\Support\Currencies::forLocale());
        $prices = $service->pivot?->prices;

        if (is_string($prices)) {
            $prices = json_decode($prices, true);
        }

        if (! is_array($prices) || $prices === []) {
            return null;
        }

        if (isset($prices[$currency]) && $prices[$currency] !== null && $prices[$currency] !== '') {
            return (float) $prices[$currency];
        }

        if (! $this->auto_convert_prices) {
            return null;
        }

        foreach ($prices as $code => $amount) {
            if ($amount === null || $amount === '') {
                continue;
            }

            $converted = \App\Models\Currency::convert((float) $amount, (string) $code, $currency);

            if ($converted !== null) {
                return $converted;
            }
        }

        return null;
    }

    /**
     * Get the segments associated with this profile.
     */
    public function segments()
    {
        return $this->belongsToMany(Segment::class, 'profile_segment')
            ->withTimestamps();
    }

    /**
     * All segments this profile should display: manually assigned active
     * segments plus a synthetic "VIP" entry derived from the active
     * subscription. VIP is never stored in `profile_segment` — it mirrors
     * the existing isVip()/scopeVip() pattern that replaced the old
     * `is_vip` column.
     *
     * @return \Illuminate\Support\Collection<int, array{id: ?int, slug: string, name: string, color: string, icon: ?string, is_vip: bool}>
     */
    public function allSegments(): \Illuminate\Support\Collection
    {
        $manual = $this->segments
            ->where('is_active', true)
            ->map(fn (Segment $segment) => [
                'id' => $segment->id,
                'slug' => $segment->slug,
                'name' => $segment->name,
                'color' => $segment->color,
                'icon' => $segment->icon,
                'is_vip' => false,
            ])
            ->values();

        if ($this->isVip()) {
            $manual->push([
                'id' => null,
                'slug' => 'vip',
                'name' => 'VIP',
                'color' => '#FFB700',
                'icon' => 'star',
                'is_vip' => true,
            ]);
        }

        return $manual;
    }

    /**
     * Get the ratings for this profile.
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get the profile views for this profile.
     */
    public function views()
    {
        return $this->hasMany(ProfileView::class);
    }

    /**
     * Get click views for this profile.
     */
    public function clickViews()
    {
        return $this->hasMany(ProfileView::class)->where('type', ProfileView::TYPE_CLICK);
    }

    /**
     * Get impression views for this profile.
     */
    public function impressionViews()
    {
        return $this->hasMany(ProfileView::class)->where('type', ProfileView::TYPE_IMPRESSION);
    }

    /**
     * Get all subscriptions for this profile.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the active subscription for this profile.
     */
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->latest('ends_at');
    }

    /**
     * Check if the profile has an active subscription.
     *
     * Callers listing many profiles (e.g. `ProfileList`, `CountryProfiles`,
     * `ProfileSlider`) already avoid an exists() query per row by adding
     * `->withExists('activeSubscription as is_vip')` to their query. When
     * that raw attribute is present we reuse it instead of issuing a fresh
     * query, so `isVip()`/`allSegments()` stay N+1-safe for any caller that
     * follows that existing convention.
     */
    public function hasActiveSubscription(): bool
    {
        if (array_key_exists('is_vip', $this->attributes)) {
            return (bool) $this->attributes['is_vip'];
        }

        return $this->activeSubscription()->exists();
    }

    /**
     * Check if the profile is VIP (has any active subscription).
     */
    public function isVip(): bool
    {
        return $this->hasActiveSubscription();
    }

    /**
     * Check if the profile's owner is currently online.
     *
     * Real activity always wins. Otherwise, fall back to a simulated status
     * so the site doesn't look empty: a configured share of profiles appears
     * online at a time, deterministically per profile within a rotating
     * 20-minute window so the badge doesn't flicker on every page load.
     *
     * Setting the share to 0 in the admin turns the fallback off, leaving the
     * badge to reflect genuine activity only.
     */
    public function isOnline(): bool
    {
        if ($this->user?->isOnline()) {
            return true;
        }

        // Admin setting wins; config/site.php is the fallback before the
        // setting has ever been saved.
        $share = Setting::getInt(
            'site.online_simulation_percent',
            (int) config('site.online_simulation_percent', 0)
        );

        if ($share <= 0) {
            return false;
        }

        $window = intdiv(time(), 1200);

        return (crc32($this->id . ':' . $window) % 100) < $share;
    }

    /**
     * Get the current subscription type.
     */
    public function getCurrentSubscriptionType(): ?SubscriptionType
    {
        return $this->activeSubscription?->subscriptionType;
    }

    /**
     * Scope a query to only include VIP profiles.
     */
    public function scopeVip($query)
    {
        return $query->whereHas('activeSubscription');
    }

    /**
     * Check if profile has a specific service.
     */
    public function hasService($serviceId): bool
    {
        return $this->services()->where('service_id', $serviceId)->exists();
    }

    /**
     * Toggle a service for this profile.
     */
    public function toggleService($serviceId): void
    {
        if ($this->hasService($serviceId)) {
            $this->services()->detach($serviceId);
        } else {
            $this->services()->attach($serviceId);
        }
    }

    /**
     * Scope a query to only include public profiles.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to only include approved profiles.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include verified profiles.
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Scope a query to only include archived profiles.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Check if the profile is verified.
     */
    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    /**
     * Check if the profile is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Mark the profile as verified.
     */
    public function markAsVerified(): static
    {
        $wasVerified = $this->isVerified();
        $this->verified_at = now();
        $this->save();

        // Notify user about verification
        if (!$wasVerified) {
            Notification::createForUser(
                $this->user_id,
                __('notifications.profile.verified_title'),
                __('notifications.profile.verified_message'),
                'success'
            );
        }

        return $this;
    }

    /**
     * Mark the profile as unverified.
     */
    public function markAsUnverified(): static
    {
        $wasVerified = $this->isVerified();
        $this->verified_at = null;
        $this->save();

        // Notify user about verification removal
        if ($wasVerified) {
            Notification::createForUser(
                $this->user_id,
                __('notifications.profile.unverified_title'),
                __('notifications.profile.unverified_message'),
                'warning'
            );
        }

        return $this;
    }

    /**
     * Boot the model and register event listeners.
     */
    protected static function booted(): void
    {
        static::updating(function (Profile $profile) {
            $originalStatus = $profile->getOriginal('status');
            $newStatus = $profile->status;

            // Only notify on status changes
            if ($originalStatus !== $newStatus) {
                if ($newStatus === 'approved') {
                    Notification::createForUser(
                        $profile->user_id,
                        __('notifications.profile.approved_title'),
                        __('notifications.profile.approved_message'),
                        'success'
                    );
                } elseif ($newStatus === 'rejected') {
                    Notification::createForUser(
                        $profile->user_id,
                        __('notifications.profile.rejected_title'),
                        __('notifications.profile.rejected_message'),
                        'danger'
                    );
                } elseif ($newStatus === 'pending' && $originalStatus === 'draft') {
                    // Notify admins about new profile submission
                    $admins = User::role('admin')->get();
                    foreach ($admins as $admin) {
                        Notification::createForUser(
                            $admin->id,
                            __('notifications.admin.new_profile_title'),
                            __('notifications.admin.new_profile_message', ['name' => $profile->display_name]),
                            'info'
                        );
                    }
                }
            }
        });

        // The public country/region lists carry live profile counts, which are
        // memoised because they render on every page. Any write that could move
        // a count (status, visibility, verification, city, country, deletion)
        // drops the memoised lists so the numbers never go stale.
        $forgetCountryStats = static fn () => \App\Services\CountryStatsService::flush();

        static::saved($forgetCountryStats);
        static::deleted($forgetCountryStats);
        static::restored($forgetCountryStats);
    }

    /**
     * Check if the profile belongs to a woman.
     */
    public function isWoman(): bool
    {
        return $this->user?->isFemale() ?? false;
    }

    /**
     * Check if the profile belongs to a man.
     */
    public function isMan(): bool
    {
        return $this->user?->isMale() ?? false;
    }

    /**
     * Register media collections for profile images and videos.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile-images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->useDisk('public');

        $this->addMediaCollection('profile-video')
            ->acceptsMimeTypes(['video/mp4', 'video/webm', 'video/quicktime'])
            ->singleFile()
            ->useDisk('public');
    }

    /**
     * Get the profile video.
     */
    public function getVideo(): ?\Spatie\MediaLibrary\MediaCollections\Models\Media
    {
        return $this->getFirstMedia('profile-video');
    }

    /**
     * Get the profile video URL.
     */
    public function getVideoUrl(): ?string
    {
        $video = $this->getVideo();
        return $video ? $video->getUrl() : null;
    }

    /**
     * Check if profile has a video.
     */
    public function hasVideo(): bool
    {
        return $this->getMedia('profile-video')->isNotEmpty();
    }

    /**
     * Register media conversions for different image sizes.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('profile-images');

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(600)
            ->sharpen(10)
            ->performOnCollections('profile-images');
    }

    /**
     * Get the first profile image URL.
     */
    public function getFirstImageUrl($conversion = null): ?string
    {
        $firstImage = $this->getFirstMedia('profile-images');
        
        if (!$firstImage) {
            return null;
        }
        
        return $conversion ? $firstImage->getUrl($conversion) : $firstImage->getUrl();
    }

    /**
     * Get the first profile image as thumbnail.
     */
    public function getFirstImageThumbUrl(): ?string
    {
        return $this->getFirstImageUrl('thumb');
    }

    /**
     * Get all profile images.
     */
    public function getAllImages()
    {
        return $this->getMedia('profile-images');
    }

    /**
     * Check if profile has multiple images.
     */
    public function hasMultipleImages(): bool
    {
        return $this->getMedia('profile-images')->count() > 1;
    }

    /**
     * Get the average rating for this profile, on the 1-5 star scale.
     *
     * Derived from the stored percentages rather than the whole-star mirror,
     * so a profile rated 70% averages 3.5 and not the 4 that made 70% look
     * like 80% everywhere it was displayed.
     */
    public function getAverageRating(): float
    {
        return RatingScale::toStars($this->getAveragePercentage());
    }

    /**
     * Get the average rating as the percentage it was actually given as.
     */
    public function getAveragePercentage(): float
    {
        // Prefer the eager-loaded aggregate when the query supplied one, so
        // listing pages do not issue a query per card. Tested with
        // array_key_exists rather than ??, because a profile with no ratings
        // has the key present and null — which would otherwise fall through
        // to a query for every card on the page.
        $average = array_key_exists('ratings_avg_percentage', $this->attributes)
            ? $this->attributes['ratings_avg_percentage']
            : $this->ratings()->avg('percentage');

        return round((float) ($average ?? 0), 1);
    }

    /**
     * Get the total number of ratings for this profile.
     */
    public function getTotalRatings(): int
    {
        return $this->ratings()->count();
    }

    /**
     * Get the star rating a specific user gave this profile.
     */
    public function getUserRating($userId): ?int
    {
        if (!$userId) {
            return null;
        }

        $rating = $this->ratings()->where('user_id', $userId)->first();
        return $rating ? $rating->rating : null;
    }

    /**
     * Get the percentage a specific user gave this profile.
     */
    public function getUserPercentage($userId): ?int
    {
        if (! $userId) {
            return null;
        }

        return $this->ratings()->where('user_id', $userId)->value('percentage');
    }

    /**
     * Check if a user has rated this profile.
     */
    public function hasUserRated($userId): bool
    {
        if (!$userId) {
            return false;
        }

        return $this->ratings()->where('user_id', $userId)->exists();
    }

    /**
     * Reasons a rating attempt can be refused. Returned by rateBy() so callers
     * can map them onto their own translated messages.
     */
    public const RATE_OK = 'ok';
    public const RATE_NOT_LOGGED_IN = 'not_logged_in';
    public const RATE_NOT_MEMBER = 'not_member';
    public const RATE_OWN_PROFILE = 'own_profile';
    public const RATE_INVALID = 'invalid';

    /**
     * Record a star rating (1–5) from a user.
     *
     * This is the single authoritative implementation shared by the
     * ProfileRating and MemberRatings Livewire components — previously each had
     * its own copy with different authorization rules (only one checked that the
     * rater was a member, and neither prevented rating your own profile).
     *
     * @return string One of the self::RATE_* constants.
     */
    public function rateBy(?User $user, int $stars): string
    {
        if ($stars < 1 || $stars > 5) {
            return self::RATE_INVALID;
        }

        return $this->rateByPercentage($user, $stars * 20);
    }

    /**
     * Record a rating given as a percentage (1-100).
     *
     * The percentage is what the member actually chose, so it is what gets
     * stored. `rating` is written alongside it as the whole-star mirror that
     * ordering queries and the admin table read.
     *
     * @return string One of the self::RATE_* constants.
     */
    public function rateByPercentage(?User $user, int $percentage): string
    {
        if (! $user) {
            return self::RATE_NOT_LOGGED_IN;
        }

        if ($percentage < 1 || $percentage > 100) {
            return self::RATE_INVALID;
        }

        // Only male members rate profiles; providers cannot rate.
        if (! $user->isMale()) {
            return self::RATE_NOT_MEMBER;
        }

        if ($this->user_id === $user->id) {
            return self::RATE_OWN_PROFILE;
        }

        $stars = RatingScale::toWholeStars($percentage);
        $isNewRating = ! $this->ratings()->where('user_id', $user->id)->exists();

        Rating::updateOrCreate(
            ['profile_id' => $this->id, 'user_id' => $user->id],
            ['rating' => $stars, 'percentage' => $percentage]
        );

        if ($isNewRating && $this->user_id) {
            Notification::createForUser(
                $this->user_id,
                __('notifications.rating.received_title'),
                __('notifications.rating.received_message', ['stars' => $stars]),
                $percentage >= RatingScale::THRESHOLD_GOOD
                    ? 'success'
                    : ($percentage >= RatingScale::THRESHOLD_FAIR ? 'info' : 'warning')
            );
        }

        return self::RATE_OK;
    }
}
