<?php

namespace App\Services\Scraping;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Minimal robots.txt reader: the directives that actually constrain a
 * well-behaved crawler.
 *
 * Only the group matching our user agent is read, falling back to `*`, which
 * is how the standard says a crawler should pick its group.
 */
class RobotsTxt
{
    /**
     * @param array<int, string> $disallow
     * @param array<int, string> $allow
     */
    public function __construct(
        public readonly array $disallow = [],
        public readonly array $allow = [],
        public readonly ?float $crawlDelay = null,
        public readonly bool $fetched = false,
        /** @var array<int, string> */
        public readonly array $sitemaps = [],
    ) {
    }

    public static function fetch(string $baseUrl, string $userAgent, int $timeout = 15): self
    {
        $url = rtrim($baseUrl, '/') . '/robots.txt';

        try {
            $response = Http::withHeaders(['User-Agent' => $userAgent])
                ->timeout($timeout)
                ->get($url);
        } catch (Throwable) {
            // A robots.txt we cannot reach is not permission to ignore it, but
            // it is also not a reason to fail the run; treat it as empty.
            return new self();
        }

        if (! $response->successful()) {
            return new self(fetched: true);
        }

        return self::parse($response->body(), $userAgent);
    }

    public static function parse(string $body, string $userAgent): self
    {
        $groups = [];
        $current = [];
        $sitemaps = [];

        foreach (preg_split('/\R/', $body) as $line) {
            $line = trim(Str::before($line, '#'));

            if ($line === '') {
                continue;
            }

            [$field, $value] = array_pad(explode(':', $line, 2), 2, '');
            $field = strtolower(trim($field));
            $value = trim($value);

            // Sitemap je globální direktiva, ne součást skupiny agenta —
            // sbírá se proto dřív, než se řeší, do jaké skupiny řádek patří.
            if ($field === 'sitemap') {
                if ($value !== '') {
                    $sitemaps[] = $value;
                }

                continue;
            }

            if ($field === 'user-agent') {
                // A second agent line straight after another starts a shared
                // group rather than a new one.
                if ($current !== [] && ($current['seen_rule'] ?? false)) {
                    $groups[] = $current;
                    $current = [];
                }

                $current['agents'][] = strtolower($value);

                continue;
            }

            if ($current === []) {
                continue;
            }

            $current['seen_rule'] = true;

            match ($field) {
                'disallow' => $current['disallow'][] = $value,
                'allow' => $current['allow'][] = $value,
                'crawl-delay' => $current['crawl_delay'] = (float) str_replace(',', '.', $value),
                default => null,
            };
        }

        if ($current !== []) {
            $groups[] = $current;
        }

        $agent = strtolower($userAgent);
        $match = null;
        $wildcard = null;

        foreach ($groups as $group) {
            foreach ($group['agents'] ?? [] as $groupAgent) {
                if ($groupAgent === '*') {
                    $wildcard ??= $group;
                } elseif (str_contains($agent, $groupAgent)) {
                    $match ??= $group;
                }
            }
        }

        $chosen = $match ?? $wildcard ?? [];

        return new self(
            disallow: array_values(array_filter($chosen['disallow'] ?? [], fn ($p) => $p !== '')),
            allow: $chosen['allow'] ?? [],
            crawlDelay: $chosen['crawl_delay'] ?? null,
            fetched: true,
            sitemaps: array_values(array_unique($sitemaps)),
        );
    }

    /** Whether a path may be fetched. Longest matching rule wins, Allow ties. */
    public function allows(string $path): bool
    {
        $path = '/' . ltrim($path, '/');

        $longestDisallow = 0;
        foreach ($this->disallow as $rule) {
            if ($this->matches($rule, $path)) {
                $longestDisallow = max($longestDisallow, strlen($rule));
            }
        }

        if ($longestDisallow === 0) {
            return true;
        }

        foreach ($this->allow as $rule) {
            if ($this->matches($rule, $path) && strlen($rule) >= $longestDisallow) {
                return true;
            }
        }

        return false;
    }

    private function matches(string $rule, string $path): bool
    {
        if ($rule === '') {
            return false;
        }

        // Only the two wildcards the standard defines.
        $pattern = preg_quote($rule, '#');
        $pattern = str_replace(['\*', '\$'], ['.*', '$'], $pattern);

        return (bool) preg_match('#^' . $pattern . '#', $path);
    }

    /**
     * Sitemapy, které web sám ohlásil.
     *
     * @return array<int, string>
     */
    public function sitemaps(): array
    {
        return $this->sitemaps;
    }

    /** @return array{disallow: array, allow: array, crawl_delay: ?float, fetched: bool, sitemaps: array} */
    public function toArray(): array
    {
        return [
            'disallow' => $this->disallow,
            'allow' => $this->allow,
            'crawl_delay' => $this->crawlDelay,
            'fetched' => $this->fetched,
            'sitemaps' => $this->sitemaps,
        ];
    }
}
