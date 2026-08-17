<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One scraped entity, staged for review.
 *
 * A row lands here as `pending` and only becomes a Profile when somebody
 * approves it. Nothing published is ever a direct side effect of a run.
 */
class ScrapeItem extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_IMPORTED = 'imported';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'scrape_source_id',
        'scrape_run_id',
        'source_url',
        'external_id',
        'content_hash',
        'raw',
        'normalized',
        'images',
        'status',
        'imported_profile_id',
        'imported_at',
        'error',
        'duplicate_profile_id',
        'duplicate_item_id',
        'duplicate_reason',
        'duplicate_checked_at',
        'unknown_values_at',
    ];

    protected function casts(): array
    {
        return [
            'raw' => 'array',
            'normalized' => 'array',
            'images' => 'array',
            'imported_at' => 'datetime',
            'duplicate_checked_at' => 'datetime',
            'unknown_values_at' => 'datetime',
        ];
    }

    /**
     * Whether this item mentions something our catalogue does not know.
     *
     * Such an item is approved only through a second confirmation; the review
     * screen offers "fill in the missing values" first, because approving it
     * as it stands means importing a profile with those values thrown away.
     */
    public function hasUnknownValues(): bool
    {
        return $this->unknown_values_at !== null;
    }

    /** Same flag, read after the gap has been filled. */
    public function wasBlockedByUnknownValue(): bool
    {
        return $this->unknown_values_at !== null;
    }

    public function scopeWithUnknownValues($query)
    {
        return $query->whereNotNull('unknown_values_at');
    }

    /**
     * Somebody we already have, found by the duplicate check.
     *
     * A snapshot from when the check last ran — the queue carries an action to
     * take it again.
     */
    public function duplicateProfile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'duplicate_profile_id');
    }

    public function duplicateItem(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicate_item_id');
    }

    public function hasDuplicate(): bool
    {
        return $this->duplicate_profile_id !== null || $this->duplicate_item_id !== null;
    }

    public function duplicateLabel(): ?string
    {
        if (! $this->hasDuplicate()) {
            return null;
        }

        $reason = \App\Services\Scraping\DuplicateFinder::reasonLabels()[$this->duplicate_reason] ?? $this->duplicate_reason;

        return $this->duplicate_profile_id
            ? 'profil #' . $this->duplicate_profile_id . ' — ' . $reason
            : 'položka #' . $this->duplicate_item_id . ' — ' . $reason;
    }

    public function scopePossibleDuplicates($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('duplicate_profile_id')->orWhereNotNull('duplicate_item_id');
        });
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Ke kontrole',
            self::STATUS_APPROVED => 'Schváleno',
            self::STATUS_REJECTED => 'Zamítnuto',
            self::STATUS_IMPORTED => 'Importováno',
            self::STATUS_FAILED => 'Chyba',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(ScrapeSource::class, 'scrape_source_id');
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(ScrapeRun::class, 'scrape_run_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'imported_profile_id');
    }

    public function value(string $field, mixed $default = null): mixed
    {
        return $this->normalized[$field] ?? $default;
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
