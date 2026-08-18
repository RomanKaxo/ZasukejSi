<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
            && $this->isWithinWindow()
            && ($this->next_run_at === null || $this->next_run_at->isPast());
    }

    /**
     * Whether automatic runs are allowed right now.
     *
     * Scraping somebody else's site during their busiest hour is both rude and
     * the surest way to be noticed and blocked; the small hours cost them
     * nothing. The window covers manual runs never — when a person is sitting
     * there waiting, the answer to „may I" is obviously yes.
     *
     * An hour window is read as „from, up to but not including to", so 2–6
     * means 02:00 to 05:59. From and to being equal means the whole day, which
     * is the same as not setting it.
     */
    public function isWithinWindow(?Carbon $at = null): bool
    {
        $at = $at ?? now();

        $days = $this->windowDays();

        // Carbon counts Sunday as 0; the setting uses 1–7 with Monday first,
        // which is how a Czech operator reads a week.
        if ($days !== [] && ! in_array($at->dayOfWeekIso, $days, true)) {
            return false;
        }

        $from = $this->windowHour('run_window_from');
        $to = $this->windowHour('run_window_to');

        if ($from === null || $to === null || $from === $to) {
            return true;
        }

        $hour = (int) $at->format('G');

        // A window that wraps midnight — 22 to 6 — is the common case here,
        // so it cannot be an afterthought.
        return $from < $to
            ? ($hour >= $from && $hour < $to)
            : ($hour >= $from || $hour < $to);
    }

    /** @return array<int, int> ISO weekday numbers the source may run on. */
    public function windowDays(): array
    {
        $value = $this->setting('run_days');

        if (is_string($value)) {
            $value = array_filter(array_map('trim', explode(',', $value)), fn ($v) => $v !== '');
        }

        if (! is_array($value)) {
            return [];
        }

        $days = array_values(array_filter(
            array_map('intval', $value),
            fn (int $day) => $day >= 1 && $day <= 7,
        ));

        // All seven is the same as no restriction, and saying so keeps the
        // „is it limited?" question answerable by one empty check.
        return count($days) === 7 ? [] : $days;
    }

    /** An hour setting as 0–23, or null when it is not usable. */
    private function windowHour(string $key): ?int
    {
        $value = $this->setting($key);

        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        $hour = (int) $value;

        return $hour >= 0 && $hour <= 23 ? $hour : null;
    }

    /** Když se okno zavře, tohle je nejbližší chvíle, kdy se zase otevře. */
    public function windowOpensAt(?Carbon $at = null): ?Carbon
    {
        $cursor = ($at ?? now())->copy()->startOfHour();

        // Nejvýš týden dopředu: dál už to není „brzy" a znamenalo by to, že
        // je nastavení špatně.
        for ($i = 0; $i < 24 * 7; $i++) {
            $cursor->addHour();

            if ($this->isWithinWindow($cursor)) {
                return $cursor;
            }
        }

        return null;
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

        // Uchovat staženou stránku, aby šlo zkoušet selektory bez dalšího
        // dotazu na cizí web.
        'keep_snapshot' => true,

        // Zastavení běhu, když najednou většina stránek nevrací nic —
        // předělaný web rozbije všechny selektory naráz.
        'redesign_guard' => true,
        'redesign_min_items' => 5,
        'redesign_ratio' => 0.8,

        // Kontrola, že importované profily na zdroji pořád jsou.
        'existence_confirmations' => 2,
        'existence_interval_hours' => 24,
        'existence_batch' => 100,

        // Kdy se smí spouštět plánovaný běh. Prázdné = kdykoli.
        'run_window_from' => null,
        'run_window_to' => null,
        'run_days' => null,
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
