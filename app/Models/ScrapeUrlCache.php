<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * What a URL last gave us, so the next run can ask conditionally.
 *
 * Written by {@see \App\Services\Scraping\HttpFetcher}; nothing else should
 * touch it. Losing a row costs one full download, never correctness.
 */
class ScrapeUrlCache extends Model
{
    protected $table = 'scrape_url_cache';

    protected $fillable = [
        'scrape_source_id',
        'url_hash',
        'url',
        'etag',
        'last_modified',
        'content_hash',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return ['fetched_at' => 'datetime'];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(ScrapeSource::class, 'scrape_source_id');
    }

    public static function hashFor(string $url): string
    {
        return sha1($url);
    }

    /** The row for a URL, or null when we have never fetched it. */
    public static function for(int $sourceId, string $url): ?self
    {
        return static::query()
            ->where('scrape_source_id', $sourceId)
            ->where('url_hash', self::hashFor($url))
            ->first();
    }

    /** Headers that turn a request into „only if it changed". */
    public function conditionalHeaders(): array
    {
        $headers = [];

        if (filled($this->etag)) {
            $headers['If-None-Match'] = $this->etag;
        }

        if (filled($this->last_modified)) {
            $headers['If-Modified-Since'] = $this->last_modified;
        }

        return $headers;
    }
}
