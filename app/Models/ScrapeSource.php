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
        'settings',
        'notes',
        'robots_checked_at',
        'robots_rules',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'settings' => 'array',
            'robots_rules' => 'array',
            'robots_checked_at' => 'datetime',
        ];
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
