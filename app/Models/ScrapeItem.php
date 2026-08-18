<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'attempts',
        'last_attempt_at',
        'retry_after',
        'missing_since',
        'missing_checks',
        'missing_checked_at',
        'missing_resolution',
        'missing_resolved_at',
        'missing_resolved_by',
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
            'attempts' => 'integer',
            'last_attempt_at' => 'datetime',
            'retry_after' => 'datetime',
            'missing_since' => 'datetime',
            'missing_checks' => 'integer',
            'missing_checked_at' => 'datetime',
            'missing_resolved_at' => 'datetime',
        ];
    }

    /**
     * How long to wait before trying a failed page again.
     *
     * Growing, because whatever broke the first time — a timeout, a hiccup on
     * their side, a rate limit — is rarely fixed a second later, and asking
     * again straight away is how a struggling site gets pushed over.
     *
     * @var array<int, int> attempt number => minutes
     */
    public const RETRY_BACKOFF = [1 => 15, 2 => 60, 3 => 360, 4 => 1440];

    /** When the next attempt is due, or null once we have stopped trying. */
    public static function nextRetryAt(int $attempts): ?\Illuminate\Support\Carbon
    {
        $minutes = self::RETRY_BACKOFF[$attempts] ?? null;

        return $minutes === null ? null : now()->addMinutes($minutes);
    }

    /** What the operator decided about a profile that vanished from its source. */
    public const MISSING_KEPT = 'kept';
    public const MISSING_REMOVED = 'removed';

    /**
     * Whether this profile is waiting for somebody to say what to do.
     *
     * Confirmed gone, still on our site, nobody has decided yet.
     */
    public function isAwaitingRemovalDecision(): bool
    {
        return $this->missing_since !== null
            && $this->missing_resolution === null
            && $this->imported_profile_id !== null;
    }

    /** Profiles the source no longer has, still waiting for a decision. */
    public function scopeMissingAtSource($query)
    {
        return $query
            ->whereNotNull('missing_since')
            ->whereNull('missing_resolution')
            ->whereNotNull('imported_profile_id');
    }

    /** Failed pages whose next attempt has come due. */
    public function scopeDueForRetry($query, int $sourceId)
    {
        return $query
            ->where('scrape_source_id', $sourceId)
            ->where('status', self::STATUS_FAILED)
            ->whereNotNull('retry_after')
            ->where('retry_after', '<=', now());
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

    /** Co se na zdroji mezi běhy změnilo, od nejnovějšího. */
    public function revisions(): HasMany
    {
        return $this->hasMany(ScrapeItemRevision::class)->latest();
    }

    /**
     * Whether the source has moved since we made a profile out of this.
     *
     * The profile is then a snapshot of a page that no longer says the same
     * thing — which is not wrong, exactly, but is worth knowing before anybody
     * relies on the price.
     */
    public function hasChangedSinceImport(): bool
    {
        if ($this->imported_at === null) {
            return false;
        }

        return $this->revisions()->where('created_at', '>', $this->imported_at)->exists();
    }

    /** Importované položky, u kterých se zdroj od importu změnil. */
    public function scopeChangedSinceImport($query)
    {
        return $query
            ->whereNotNull('imported_profile_id')
            ->whereNotNull('imported_at')
            ->whereHas('revisions', fn ($q) => $q->whereColumn(
                'scrape_item_revisions.created_at',
                '>',
                'scrape_items.imported_at',
            ));
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
