<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        'content',
        'availability_hours',
        'local_prices',
        'global_prices',
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
            'availability_hours' => 'array',
            'local_prices' => 'array',
            'global_prices' => 'array',
            'contacts' => 'array',
            'verified_at' => 'datetime',
            'is_public' => 'boolean',
            'is_porn_actress' => 'boolean',
            'incall' => 'boolean',
            'outcall' => 'boolean',
        ];
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
            ->withTimestamps();
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
     */
    public function hasActiveSubscription(): bool
    {
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
     * Get the average rating for this profile.
     */
    public function getAverageRating(): float
    {
        return round($this->ratings()->avg('rating') ?? 0, 1);
    }

    /**
     * Get the total number of ratings for this profile.
     */
    public function getTotalRatings(): int
    {
        return $this->ratings()->count();
    }

    /**
     * Get the rating from a specific user.
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
     * Check if a user has rated this profile.
     */
    public function hasUserRated($userId): bool
    {
        if (!$userId) {
            return false;
        }
        
        return $this->ratings()->where('user_id', $userId)->exists();
    }
}
