<?php

namespace App\Models;

use App\Support\RatingScale;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    /** @use HasFactory<\Database\Factories\RatingFactory> */
    use HasFactory;

    protected $table = 'profile_ratings';

    protected $fillable = [
        'profile_id',
        'user_id',
        'rating',
        'percentage',
    ];

    protected $casts = [
        'rating' => 'integer',
        'percentage' => 'integer',
    ];

    /**
     * Keep the two columns from ever contradicting each other, whichever one
     * the caller set. The percentage is authoritative; when only stars are
     * given (older code, seeders, hand-written rows) it is derived from them.
     */
    protected static function booted(): void
    {
        static::saving(function (Rating $rating) {
            if ($rating->isDirty('percentage') && $rating->percentage > 0) {
                $rating->rating = RatingScale::toWholeStars((float) $rating->percentage);

                return;
            }

            if ((int) $rating->percentage === 0 && $rating->rating > 0) {
                $rating->percentage = RatingScale::clamp($rating->rating * 20);
            }
        });
    }

    /**
     * The percentage on the 1-5 star scale, as a float.
     *
     * `rating` is the whole-star mirror kept for ordering and the admin table;
     * this is the faithful projection used for display.
     */
    public function getStarsAttribute(): float
    {
        return RatingScale::toStars((float) $this->percentage);
    }

    /** Bar colour for this rating. */
    public function getColorAttribute(): string
    {
        return RatingScale::color((float) $this->percentage);
    }

    /**
     * Get the profile that owns the rating.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the user that created the rating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
