<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A site the scraper knows how to read.
 *
 * Everything that differs between sites lives here or in the field maps, so a
 * new site is a row plus selectors rather than a new class.
 */
class ScrapeSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'base_url',
        'adapter',
        'is_enabled',
        'schedule_hours',
        'next_run_at',
        'schedule_pages',
        'schedule_limit',
        'settings',
        'notes',
        'robots_checked_at',
        'robots_rules',
        'consecutive_failures',
        'paused_at',
        'paused_reason',
        'last_success_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'settings' => 'array',
            'robots_rules' => 'array',
            'robots_checked_at' => 'datetime',
            'schedule_hours' => 'integer',
            'schedule_pages' => 'integer',
            'schedule_limit' => 'integer',
            'next_run_at' => 'datetime',
            'consecutive_failures' => 'integer',
            'paused_at' => 'datetime',
            'last_success_at' => 'datetime',
        ];
    }

    /**
     * Whether this source runs on its own.
     *
     * A disabled source never does, whatever the interval says — the same
     * guard the runner applies.
     */
    public function isScheduled(): bool
    {
        return $this->is_enabled
            && ! $this->isPaused()
            && $this->schedule_hours !== null
            && $this->schedule_hours > 0;
    }

    /**
     * Whether the scheduler has taken this source out of rotation.
     *
     * A site that has started refusing us does not get better by being asked
     * every hour for a week — it gets us blocked harder. The pause is a flag,
     * not a switch off: the source keeps its settings, the operator sees why,
     * and a manual run clears it the moment it works again.
     */
    public function isPaused(): bool
    {
        return $this->paused_at !== null;
    }

    /** How many failures in a row take a source out of rotation. */
    public function failureThreshold(): int
    {
        return max(1, (int) $this->setting('failure_threshold', 3));
    }

    /** A run finished without an error: the source is healthy again. */
    public function recordSuccess(): void
    {
        $this->forceFill([
            'consecutive_failures' => 0,
            'paused_at' => null,
            'paused_reason' => null,
            'last_success_at' => now(),
        ])->save();
    }

    /**
     * A run failed. Returns true when this was the failure that paused it.
     */
    public function recordFailure(string $reason): bool
    {
        $failures = (int) $this->consecutive_failures + 1;
        $pause = $this->setting('auto_pause', true) && $failures >= $this->failureThreshold();

        $this->forceFill([
            'consecutive_failures' => $failures,
            'paused_at' => $pause ? now() : $this->paused_at,
            // Trimmed: this is shown in a table cell, and a stack-trace-length
            // message there makes the whole row unreadable.
            'paused_reason' => $pause ? mb_substr($reason, 0, 500) : $this->paused_reason,
        ])->save();

        return $pause && $this->wasChanged('paused_at');
    }

    /** Put a paused source back in rotation. */
    public function resume(): void
    {
        $this->forceFill([
            'paused_at' => null,
            'paused_reason' => null,
            'consecutive_failures' => 0,
        ])->save();
    }

    public function isDue(): bool
    {
        return $this->isScheduled()
            && ($this->next_run_at === null || $this->next_run_at->isPast());
    }

    /**
     * Move the source to its next slot.
     *
     * Counted from now rather than from the previous slot, so a run that took
     * an hour does not immediately come due again.
     */
    public function scheduleNextRun(): void
    {
        if (! $this->isScheduled()) {
            $this->forceFill(['next_run_at' => null])->save();

            return;
        }

        $this->forceFill(['next_run_at' => now()->addHours($this->schedule_hours)])->save();
    }

    /** @param  \Illuminate\Database\Eloquent\Builder  $query */
    public function scopeDue($query)
    {
        return $query
            ->where('is_enabled', true)
            ->whereNull('paused_at')
            ->whereNotNull('schedule_hours')
            ->where('schedule_hours', '>', 0)
            ->where(function ($q) {
                $q->whereNull('next_run_at')->orWhere('next_run_at', '<=', now());
            });
    }

    /**
     * Defaults chosen to be polite rather than fast. A source can raise the
     * delay but the fetcher will not go below whatever robots.txt asks for.
     */
    public const DEFAULT_SETTINGS = [
        'user_agent' => 'ZasukejSiBot/1.0 (+https://zasukejsi.cz/bot)',
        'crawl_delay' => 5,
        'timeout' => 25,
        'max_pages' => 5,
        'listing_path' => '/',
        'detail_link_selector' => 'a',
        'detail_url_pattern' => null,
        'pagination_param' => 'page',
        'external_id_pattern' => '/(\d+)\/?$/',
        'image_selector' => null,
        'image_attribute' => 'href',
        'image_limit' => 10,
        'respect_robots' => true,

        // Jak se hledají adresy detailů: procházením výpisu, nebo ze sitemapy.
        'discovery' => 'listing',
        'sitemap_url' => null,
        'sitemap_changed_only' => true,

        // „paged" počítá adresu z čísla stránky, „next_link" ji čte z odkazu
        // na další stránku — sites with opaque cursors have no page numbers.
        'pagination_mode' => 'paged',
        'next_link_selector' => null,

        'conditional_requests' => true,
        'proxy' => null,
        'auto_pause' => true,
        'failure_threshold' => 3,

        // Kolikrát se zkusí stránka, která se nepodařila stáhnout.
        'max_attempts' => 5,
    ];

    public function fieldMaps(): HasMany
    {
        return $this->hasMany(ScrapeFieldMap::class)->orderBy('sort_order');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(ScrapeRun::class)->latest();
    }

    public function items(): HasMany
    {
        return $this->hasMany(ScrapeItem::class);
    }

    /** A setting with the shipped default behind it. */
    public function setting(string $key, mixed $default = null): mixed
    {
        $value = $this->settings[$key] ?? null;

        if ($value === null || $value === '') {
            return self::DEFAULT_SETTINGS[$key] ?? $default;
        }

        return $value;
    }

    /**
     * The delay to keep between requests: whatever robots.txt asks for, or the
     * configured value, whichever is longer.
     */
    public function effectiveCrawlDelay(): float
    {
        $configured = (float) $this->setting('crawl_delay', 5);
        $fromRobots = (float) ($this->robots_rules['crawl_delay'] ?? 0);

        return max($configured, $fromRobots);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
}
