<?php

namespace App\Services\Scraping;

use App\Models\ScrapeSource;
use Throwable;

/**
 * Looks at a site once and reports what it would take to scrape it.
 *
 * Adding a source was archaeology: open the page, read the markup, guess a
 * selector, run a trial, guess again. Most of that is mechanical — whether
 * there is a sitemap, whether the site publishes JSON-LD, which repeated link
 * shape leads to a detail page — and a machine is better at it than a person
 * squinting at minified HTML.
 *
 * Nothing here writes anything. It answers, in one place, the questions the
 * operator would otherwise answer by hand, and says plainly when it does not
 * know.
 */
class SiteProbe
{
    /** How many candidate link patterns to report. */
    private const MAX_CANDIDATES = 5;

    /** A pattern needs at least this many links to look like a listing. */
    private const MIN_REPEATS = 3;

    public function __construct(
        private readonly HttpFetcher $fetcher,
        private readonly SitemapReader $sitemap,
        private readonly FieldExtractor $extractor,
    ) {
    }

    /**
     * @return array{
     *     url: string,
     *     robots: array<string, mixed>,
     *     sitemap: array<string, mixed>,
     *     links: array<int, array<string, mixed>>,
     *     structured: array<int, string>,
     *     embedded: array<int, string>,
     *     client_rendered: bool,
     *     encoding: array<string, mixed>,
     *     notes: array<int, string>,
     * }
     */
    public function run(ScrapeSource $source, ?string $listingUrl = null): array
    {
        $url = $listingUrl ?: rtrim($source->base_url, '/') . '/' . ltrim((string) $source->setting('listing_path'), '/');

        $report = [
            'url' => $url,
            'robots' => $this->robots($source),
            'sitemap' => $this->sitemap($source),
            'links' => [],
            'structured' => [],
            'embedded' => [],
            'client_rendered' => false,
            'encoding' => [],
            'notes' => [],
        ];

        try {
            $html = $this->fetcher->get($source, $url);
        } catch (Throwable $e) {
            $report['notes'][] = 'Stránku se nepodařilo stáhnout: ' . $e->getMessage();

            return $report;
        }

        $report['encoding'] = $this->encoding($html);
        $report['links'] = $this->linkCandidates($html, $source);
        $report['structured'] = $this->extractor->structuredKeys($html);
        $report['embedded'] = $this->extractor->embeddedKeys($html);
        $report['client_rendered'] = $this->extractor->looksClientRendered($html);

        if ($report['links'] === []) {
            $report['notes'][] = 'Na stránce se nenašla žádná skupina podobných odkazů. '
                . 'Buď to není výpis, nebo se obsah skládá až v prohlížeči.';
        }

        if ($report['client_rendered']) {
            $report['notes'][] = $report['embedded'] === []
                ? 'Stránka se skládá až v prohlížeči a nemá v sobě vložená data. Selektory tu nenajdou nic '
                    . 'a scraper JavaScript nespouští — pomůže jedině stahování přes externí renderer.'
                : 'Stránka se skládá až v prohlížeči, ale data v sobě veze. Použijte selektory „json:…" '
                    . 'z výpisu níž — jsou to hodnoty z vlastní datové struktury webu, takže přežijí i redesign.';
        }

        if ($report['structured'] === []) {
            $report['notes'][] = 'Web nezveřejňuje JSON-LD ani meta značky, takže se neobejdete bez selektorů.';
        }

        if (($report['sitemap']['found'] ?? false) && $report['links'] !== []) {
            $report['notes'][] = 'Web má sitemapu — přepnutí „Zdroj adres" na sitemapu ušetří hledání selektoru odkazů i stránkování.';
        }

        return $report;
    }

    /** @return array<string, mixed> */
    private function robots(ScrapeSource $source): array
    {
        try {
            $robots = $this->fetcher->robotsFor($source);
        } catch (Throwable $e) {
            return ['read' => false, 'error' => $e->getMessage()];
        }

        return [
            'read' => true,
            'disallow' => count($robots->disallow),
            'crawl_delay' => $robots->crawlDelay,
            'effective_delay' => $source->effectiveCrawlDelay(),
            'sitemaps' => $robots->sitemaps(),
        ];
    }

    /** @return array<string, mixed> */
    private function sitemap(ScrapeSource $source): array
    {
        foreach ($this->sitemap->candidates($source) as $candidate) {
            try {
                $urls = $this->sitemap->urls($source, $candidate);
            } catch (Throwable) {
                continue;
            }

            if ($urls !== []) {
                return [
                    'found' => true,
                    'url' => $candidate,
                    'count' => count($urls),
                    'sample' => array_slice($urls, 0, 3),
                ];
            }
        }

        return ['found' => false];
    }

    /** @return array<string, mixed> */
    private function encoding(string $html): array
    {
        $encoding = new PageEncoding();

        return [
            'declared' => $encoding->declaredCharset($html) ?? 'neuvedeno',
            'valid_utf8' => mb_check_encoding($html, 'UTF-8'),
        ];
    }

    /**
     * Groups of links that look like they lead to detail pages.
     *
     * The signal is repetition: a listing shows the same kind of link many
     * times, with addresses of the same shape. Navigation and footers repeat
     * too, so what separates them is how many there are and whether the paths
     * carry something that varies — usually an id or a slug.
     *
     * @return array<int, array<string, mixed>>
     */
    public function linkCandidates(string $html, ScrapeSource $source): array
    {
        $xpath = $this->extractor->xpathFor($html);
        $nodes = @$xpath->query('//a[@href]');

        if ($nodes === false || $nodes->length === 0) {
            return [];
        }

        $host = parse_url($source->base_url, PHP_URL_HOST);
        $groups = [];

        foreach ($nodes as $node) {
            $href = trim($node->getAttribute('href'));

            if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                continue;
            }

            $path = parse_url($href, PHP_URL_PATH);
            $linkHost = parse_url($href, PHP_URL_HOST);

            // Somebody else's site is not this site's listing.
            if ($path === null || ($linkHost !== null && $host !== null && $linkHost !== $host)) {
                continue;
            }

            $shape = $this->pathShape($path);

            if ($shape === null) {
                continue;
            }

            $groups[$shape]['count'] = ($groups[$shape]['count'] ?? 0) + 1;
            $groups[$shape]['sample'] ??= $href;
            $groups[$shape]['class'] ??= $this->classSelector($node);
        }

        $groups = array_filter($groups, fn (array $group) => $group['count'] >= self::MIN_REPEATS);

        uasort($groups, fn (array $a, array $b) => $b['count'] <=> $a['count']);

        $out = [];

        foreach (array_slice($groups, 0, self::MAX_CANDIDATES, true) as $shape => $group) {
            $out[] = [
                'shape' => $shape,
                'count' => $group['count'],
                'sample' => $group['sample'],
                // What to paste into the source: the selector narrows the
                // links, the pattern throws away whatever it still catches.
                'detail_link_selector' => $group['class'] ?? 'a',
                'detail_url_pattern' => $this->patternFor($shape),
            ];
        }

        return $out;
    }

    /**
     * A path with its variable parts replaced, so links to different profiles
     * collapse into one group.
     *
     * Returns null for paths too short to be a detail page — a listing's own
     * address and the home page repeat plenty and lead nowhere.
     */
    private function pathShape(string $path): ?string
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/')), fn ($s) => $s !== ''));

        if ($segments === []) {
            return null;
        }

        $shaped = array_map(function (string $segment) {
            if (preg_match('/^\d+$/', $segment)) {
                return '{číslo}';
            }

            // A slug: several words joined by dashes, or a long single word.
            if (preg_match('/^[\p{L}\d]+(-[\p{L}\d]+){1,}$/u', $segment) || mb_strlen($segment) > 12) {
                return '{název}';
            }

            return $segment;
        }, $segments);

        // A shape with nothing variable in it is a section, not a detail page.
        return in_array('{číslo}', $shaped, true) || in_array('{název}', $shaped, true)
            ? '/' . implode('/', $shaped)
            : null;
    }

    /** A regular expression matching that shape, ready to paste. */
    private function patternFor(string $shape): string
    {
        $escaped = preg_quote($shape, '#');
        $escaped = str_replace([preg_quote('{číslo}', '#'), preg_quote('{název}', '#')], ['\d+', '[^/]+'], $escaped);

        return '#' . $escaped . '/?$#u';
    }

    /** The most specific class on the link, as a CSS selector. */
    private function classSelector(\DOMElement $node): ?string
    {
        $class = trim($node->getAttribute('class'));

        if ($class === '') {
            return null;
        }

        $first = preg_split('/\s+/', $class)[0] ?? '';

        // Utility soup like `mt-2` says nothing about what the link is.
        return preg_match('/^[a-z][a-z0-9_-]{3,}$/i', $first) ? 'a.' . $first : null;
    }
}
