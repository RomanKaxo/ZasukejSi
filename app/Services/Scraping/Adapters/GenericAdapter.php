<?php

namespace App\Services\Scraping\Adapters;

use App\Models\ScrapeSource;
use App\Services\Scraping\Contracts\SourceAdapter;
use App\Services\Scraping\FieldExtractor;
use Illuminate\Support\Str;

/**
 * Covers the common shape: a listing page with links to details, paged through
 * a query parameter. Everything it needs comes from the source's settings, so
 * adding a site of this shape takes no code.
 */
class GenericAdapter implements SourceAdapter
{
    public function __construct(protected readonly FieldExtractor $extractor = new FieldExtractor())
    {
    }

    public function listingUrl(ScrapeSource $source, int $page): string
    {
        // A run can point at one listing URL without editing the source, which
        // is what the "download from this address" action in the admin uses.
        $override = $source->setting('listing_url_override');

        $base = is_string($override) && $override !== ''
            ? $override
            : rtrim($source->base_url, '/') . '/' . ltrim((string) $source->setting('listing_path'), '/');

        $base = rtrim($base, '/');

        if ($page <= 1) {
            return $base . '/';
        }

        $param = (string) $source->setting('pagination_param');

        // A path-style pattern wins when configured, e.g. "/page/{page}/".
        $pattern = $source->setting('pagination_pattern');

        if (is_string($pattern) && $pattern !== '') {
            return $base . str_replace('{page}', (string) $page, $pattern);
        }

        return $base . '/?' . $param . '=' . $page;
    }

    public function detailUrls(ScrapeSource $source, string $listingHtml): array
    {
        $selector = (string) $source->setting('detail_link_selector');
        $pattern = $source->setting('detail_url_pattern');

        $urls = $this->hrefs($listingHtml, $selector, $source->base_url);

        if (is_string($pattern) && $pattern !== '') {
            $urls = array_filter($urls, fn ($url) => (bool) @preg_match($pattern, $url));
        }

        return array_values(array_unique($urls));
    }

    public function externalId(ScrapeSource $source, string $detailUrl): ?string
    {
        $pattern = (string) $source->setting('external_id_pattern');

        if ($pattern !== '' && @preg_match($pattern, $detailUrl, $m)) {
            return $m[1] ?? $m[0];
        }

        // Falling back to the URL keeps the unique index meaningful even when
        // the pattern does not match.
        return md5($detailUrl);
    }

    public function imageUrls(ScrapeSource $source, string $detailHtml): array
    {
        $selector = $source->setting('image_selector');

        if (! is_string($selector) || $selector === '') {
            return [];
        }

        $attribute = (string) $source->setting('image_attribute', 'href');
        $limit = (int) $source->setting('image_limit', 10);

        $urls = $this->attributes($detailHtml, $selector, $attribute, $source->base_url);
        $urls = array_values(array_unique(array_filter($urls)));

        // Sites often serve several sizes of the same photo; keep the one the
        // source asks for and drop the rest.
        $prefer = $source->setting('image_prefer_pattern');

        if (is_string($prefer) && $prefer !== '') {
            $preferred = array_values(array_filter($urls, fn ($u) => (bool) @preg_match($prefer, $u)));

            if ($preferred !== []) {
                $urls = $preferred;
            }
        }

        return $limit > 0 ? array_slice($urls, 0, $limit) : $urls;
    }

    /** @return array<int, string> */
    protected function hrefs(string $html, string $selector, string $baseUrl): array
    {
        return $this->attributes($html, $selector, 'href', $baseUrl);
    }

    /** @return array<int, string> */
    protected function attributes(string $html, string $selector, string $attribute, string $baseUrl): array
    {
        $xpath = $this->extractor->xpathFor($html);

        $map = new \App\Models\ScrapeFieldMap([
            'selector' => $selector,
            'extract' => 'attr:' . $attribute,
            'multiple' => true,
        ]);

        $values = $this->extractor->select($xpath, $map);

        if (! is_array($values)) {
            return [];
        }

        return array_map(fn ($value) => $this->absolute((string) $value, $baseUrl), $values);
    }

    protected function absolute(string $url, string $baseUrl): string
    {
        if ($url === '' || Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        if (Str::startsWith($url, '//')) {
            return (parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https') . ':' . $url;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
    }
}
