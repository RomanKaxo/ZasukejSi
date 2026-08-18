<?php

namespace App\Services\Scraping;

use App\Models\ScrapeSource;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Detail URLs straight from the site's own sitemap.
 *
 * Crawling a listing means guessing: which link is a profile, how the pages
 * are numbered, when to stop. A sitemap is the site telling us the answer —
 * every address it wants found, often with the date it last changed.
 *
 * That date is what makes a scheduled run cheap: a source can ask for only
 * what moved since the last harvest instead of walking the whole site again.
 *
 * Sitemaps are also the one discovery method that needs no selector at all,
 * which is what makes a new site a five-minute job rather than an afternoon.
 */
class SitemapReader
{
    /** How deep a sitemap index may nest before we stop following it. */
    private const MAX_DEPTH = 3;

    /** Ceiling on how many addresses one call will return. */
    private const MAX_URLS = 10000;

    public function __construct(private readonly HttpFetcher $fetcher)
    {
    }

    /**
     * Where to look when the source does not say.
     *
     * robots.txt is asked first because that is where a site is supposed to
     * announce it; the conventional paths are the fallback.
     *
     * @return array<int, string>
     */
    public function candidates(ScrapeSource $source): array
    {
        $configured = $source->setting('sitemap_url');

        if (is_string($configured) && trim($configured) !== '') {
            return [trim($configured)];
        }

        $base = rtrim($source->base_url, '/');
        $candidates = [];

        try {
            $candidates = $this->fetcher->robotsFor($source)->sitemaps();
        } catch (Throwable) {
            // A site without a readable robots.txt still usually has the file
            // in the usual place.
        }

        return array_values(array_unique(array_merge($candidates, [
            $base . '/sitemap.xml',
            $base . '/sitemap_index.xml',
        ])));
    }

    /**
     * Addresses from a sitemap, following index files.
     *
     * @param  Carbon|null  $changedSince  Only entries the site says moved after this.
     * @return array<int, string>
     */
    public function urls(ScrapeSource $source, string $sitemapUrl, ?Carbon $changedSince = null, int $depth = 0): array
    {
        if ($depth >= self::MAX_DEPTH) {
            return [];
        }

        $xml = $this->parse($this->fetcher->get($source, $sitemapUrl));

        if ($xml === null) {
            return [];
        }

        // A sitemap index points at more sitemaps; follow them.
        if ($xml->getName() === 'sitemapindex') {
            $urls = [];

            foreach ($xml->sitemap as $entry) {
                $child = trim((string) $entry->loc);

                if ($child === '') {
                    continue;
                }

                $urls = array_merge($urls, $this->urls($source, $child, $changedSince, $depth + 1));

                if (count($urls) >= self::MAX_URLS) {
                    break;
                }
            }

            return array_slice(array_values(array_unique($urls)), 0, self::MAX_URLS);
        }

        $urls = [];

        foreach ($xml->url as $entry) {
            $loc = trim((string) $entry->loc);

            if ($loc === '') {
                continue;
            }

            if ($changedSince !== null && ! $this->movedSince($entry, $changedSince)) {
                continue;
            }

            $urls[] = $loc;
        }

        return array_slice(array_values(array_unique($urls)), 0, self::MAX_URLS);
    }

    /**
     * Detail addresses for a source, filtered by its own URL pattern.
     *
     * A sitemap lists everything — articles, categories, the contact page —
     * so the source's `detail_url_pattern` is what separates a profile from
     * the rest. Without one we would queue the whole site.
     *
     * @return array<int, string>
     */
    public function detailUrls(ScrapeSource $source, ?Carbon $changedSince = null): array
    {
        $pattern = $source->setting('detail_url_pattern');
        $found = [];

        foreach ($this->candidates($source) as $candidate) {
            try {
                $found = $this->urls($source, $candidate, $changedSince);
            } catch (Throwable) {
                // Try the next candidate: a 404 on /sitemap.xml just means the
                // site keeps it somewhere else.
                continue;
            }

            if ($found !== []) {
                break;
            }
        }

        if (is_string($pattern) && $pattern !== '') {
            $found = array_values(array_filter($found, fn ($url) => (bool) @preg_match($pattern, $url)));
        }

        return $found;
    }

    /** Whether a sitemap entry claims to have changed after a date. */
    private function movedSince(\SimpleXMLElement $entry, Carbon $since): bool
    {
        $lastmod = trim((string) ($entry->lastmod ?? ''));

        // No date means we cannot rule it out, so it stays in.
        if ($lastmod === '') {
            return true;
        }

        try {
            return Carbon::parse($lastmod)->greaterThanOrEqualTo($since);
        } catch (Throwable) {
            return true;
        }
    }

    /** Parsed sitemap, or null when the response was not one. */
    private function parse(string $body): ?\SimpleXMLElement
    {
        // Velké weby posílají sitemap.xml.gz; klient nám dá bajty tak, jak jsou.
        if (str_starts_with($body, "�")) {
            $body = (string) @gzdecode($body);
        }

        $body = trim($body);

        if ($body === '') {
            return null;
        }

        $previous = libxml_use_internal_errors(true);

        try {
            $xml = simplexml_load_string($body);
        } catch (Throwable) {
            $xml = false;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if ($xml === false) {
            return null;
        }

        return in_array($xml->getName(), ['urlset', 'sitemapindex'], true) ? $xml : null;
    }
}
