<?php

namespace App\Services\Scraping;

use App\Models\ScrapeSource;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Every outbound request the scraper makes goes through here, so the rate
 * limit and the robots.txt check cannot be forgotten at a call site.
 *
 * The delay is enforced between requests to the same host, and it is the
 * larger of the configured value and what robots.txt asks for — a source can
 * be made slower than the site requests, never faster.
 */
class HttpFetcher
{
    /** Last request time per host, as a float unix timestamp. */
    private array $lastRequestAt = [];

    private array $robots = [];

    public function __construct(private readonly bool $sleep = true)
    {
    }

    public function robotsFor(ScrapeSource $source): RobotsTxt
    {
        $host = parse_url($source->base_url, PHP_URL_HOST) ?: $source->base_url;

        return $this->robots[$host] ??= RobotsTxt::fetch(
            $source->base_url,
            (string) $source->setting('user_agent'),
            (int) $source->setting('timeout'),
        );
    }

    /**
     * Fetch a URL, waiting out the crawl delay first.
     *
     * @throws RuntimeException when robots.txt disallows the path, or the
     *                          response is not a success.
     */
    public function get(ScrapeSource $source, string $url): string
    {
        if ($source->setting('respect_robots', true)) {
            $robots = $this->robotsFor($source);
            $path = parse_url($url, PHP_URL_PATH) ?: '/';

            if (! $robots->allows($path)) {
                throw new RuntimeException("robots.txt zakazuje cestu {$path}");
            }
        }

        $this->waitForTurn($source, $url);

        $response = Http::withHeaders([
            'User-Agent' => (string) $source->setting('user_agent'),
            'Accept' => 'text/html,application/xhtml+xml',
            'Accept-Language' => 'cs,en;q=0.8',
        ])
            ->timeout((int) $source->setting('timeout'))
            ->retry(2, 1500, throw: false)
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException("HTTP {$response->status()} pro {$url}");
        }

        return $response->body();
    }

    /** Fetch binary content (images), under the same rate limit. */
    public function getBinary(ScrapeSource $source, string $url): string
    {
        $this->waitForTurn($source, $url);

        $response = Http::withHeaders(['User-Agent' => (string) $source->setting('user_agent')])
            ->timeout((int) $source->setting('timeout'))
            ->retry(2, 1500, throw: false)
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException("HTTP {$response->status()} pro obrázek {$url}");
        }

        return $response->body();
    }

    /**
     * The delay applies per host, because a site's media CDN is usually a
     * different host and should not be throttled against the page requests.
     */
    private function waitForTurn(ScrapeSource $source, string $url): void
    {
        $host = parse_url($url, PHP_URL_HOST) ?: 'default';
        $delay = $source->effectiveCrawlDelay();

        if ($delay <= 0) {
            $this->lastRequestAt[$host] = microtime(true);

            return;
        }

        $last = $this->lastRequestAt[$host] ?? null;

        if ($last !== null) {
            $elapsed = microtime(true) - $last;
            $remaining = $delay - $elapsed;

            if ($remaining > 0 && $this->sleep) {
                usleep((int) round($remaining * 1_000_000));
            }
        }

        $this->lastRequestAt[$host] = microtime(true);
    }

    /** Exposed for assertions in tests. */
    public function lastRequestAt(string $host): ?float
    {
        return $this->lastRequestAt[$host] ?? null;
    }
}
