<?php

namespace App\Services\Scraping;

use App\Models\ScrapeSource;
use App\Models\ScrapeUrlCache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

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

    private readonly PageEncoding $encoding;

    public function __construct(private readonly bool $sleep = true, ?PageEncoding $encoding = null)
    {
        $this->encoding = $encoding ?? new PageEncoding();
    }

    public function robotsFor(ScrapeSource $source): RobotsTxt
    {
        $host = parse_url($source->base_url, PHP_URL_HOST) ?: $source->base_url;

        // robots.txt se stahuje stejnou cestou jako všechno ostatní, tedy
        // včetně hlaviček a proxy zdroje. Dřív si dělal vlastní dotaz jen s
        // User-Agentem — jediný požadavek v celém scraperu, který proxy
        // ignoroval, a to zrovna v situaci, kdy je proxy jediný důvod, proč
        // vůbec něco funguje. Selhalo by to navíc potichu: nedostupný
        // robots.txt se čte jako „žádná pravidla".
        RobotsTxt::using(fn (string $agent, int $timeout) => $this->request($source)->timeout($timeout));

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

        $response = $this->request($source)->get($this->fetchUrl($source, $url));

        $this->guard($source, $response, $url);

        return $this->text($response);
    }

    /**
     * The address actually requested.
     *
     * A page assembled in the browser leaves nothing in the HTML for a
     * selector to find, and running a browser here would mean shipping Chrome,
     * a queue and a lot of memory for a handful of sites. What this does
     * instead is let an operator point at a rendering service they already
     * have: the address goes through it, the rendered HTML comes back, and the
     * rest of the scraper neither knows nor cares.
     *
     * `{url}` in the endpoint is replaced by the encoded address; without it
     * the address is appended as `?url=`.
     */
    private function fetchUrl(ScrapeSource $source, string $url): string
    {
        $endpoint = $source->setting('render_endpoint');

        if (! is_string($endpoint) || trim($endpoint) === '') {
            return $url;
        }

        $endpoint = trim($endpoint);

        if (str_contains($endpoint, '{url}')) {
            return str_replace('{url}', urlencode($url), $endpoint);
        }

        return $endpoint . (str_contains($endpoint, '?') ? '&' : '?') . 'url=' . urlencode($url);
    }

    /**
     * Fetch only if the page changed since we last saw it.
     *
     * Returns null when the site says it has not. Every scheduled run used to
     * re-download every detail page in full even when not a byte had moved,
     * which on a few hundred profiles is a few hundred pointless megabytes a
     * day — ours to pay for and theirs to serve.
     *
     * Falls back to comparing a hash of the body, because plenty of sites send
     * neither ETag nor Last-Modified.
     */
    public function getIfChanged(ScrapeSource $source, string $url): ?string
    {
        if (! $source->setting('conditional_requests', true)) {
            return $this->get($source, $url);
        }

        $this->waitForTurn($source, $url);

        $cached = ScrapeUrlCache::for($source->id, $url);

        $response = $this->request($source)
            ->withHeaders($cached?->conditionalHeaders() ?? [])
            ->get($this->fetchUrl($source, $url));

        if ($response->status() === 304) {
            return null;
        }

        $this->guard($source, $response, $url);

        $body = $this->text($response);
        $hash = sha1($body);

        // The site answered 200 but sent us exactly what we already had.
        $unchanged = $cached && $cached->content_hash === $hash;

        ScrapeUrlCache::updateOrCreate(
            [
                'scrape_source_id' => $source->id,
                'url_hash' => ScrapeUrlCache::hashFor($url),
            ],
            [
                'url' => $url,
                'etag' => $response->header('ETag') ?: null,
                'last_modified' => $response->header('Last-Modified') ?: null,
                'content_hash' => $hash,
                'fetched_at' => now(),
            ],
        );

        return $unchanged ? null : $body;
    }

    /**
     * A response's body as UTF-8 text.
     *
     * Everything downstream assumes UTF-8 — the DOM parser is even told so
     * outright — so a page in windows-1250 did not fail, it quietly wrote
     * mangled diacritics into a profile. Converting here means no caller can
     * forget, exactly as with the rate limit.
     */
    private function text(Response $response): string
    {
        return $this->encoding->toUtf8($response->body(), $response->header('Content-Type') ?: null);
    }

    /** The request builder every call shares: headers, timeout, proxy. */
    private function request(ScrapeSource $source): PendingRequest
    {
        $request = Http::withHeaders($this->headers($source))
            ->timeout((int) $source->setting('timeout'))
            ->retry(2, 1500, $this->worthRetrying(...), throw: false);

        // A site that blocks the server's address is not a code problem, and
        // waiting for a new server is not a fix. Set per source, so one
        // blocked site does not route everything else through a third party.
        $proxy = $source->setting('proxy');

        if (is_string($proxy) && trim($proxy) !== '') {
            $request = $request->withOptions(['proxy' => trim($proxy)]);
        }

        return $request;
    }

    /**
     * Whether a failed attempt deserves a second one.
     *
     * Everything used to be retried, which meant a site that had blocked us
     * got asked twice for every page — the one behaviour guaranteed to make a
     * block permanent. A refusal is an answer; only a connection that never
     * arrived, a rate limit, or a server-side wobble is worth repeating.
     */
    private function worthRetrying(?Throwable $exception): bool
    {
        // A 304 is not an error, so the client has no exception to hand us —
        // and there is certainly nothing to retry about it.
        if ($exception === null) {
            return false;
        }

        if ($exception instanceof ConnectionException) {
            return true;
        }

        if (! $exception instanceof RequestException) {
            return false;
        }

        $status = $exception->response->status();

        return $status === 429 || $status >= 500;
    }

    /**
     * Turn a failing response into an exception that says what happened.
     *
     * A 429 or a 503 usually carries `Retry-After`; honouring it is the
     * difference between backing off and being banned.
     */
    private function guard(ScrapeSource $source, Response $response, string $url): void
    {
        if ($response->successful()) {
            return;
        }

        $retryAfter = $this->retryAfterSeconds($response);

        if ($retryAfter !== null) {
            $host = parse_url($url, PHP_URL_HOST) ?: $url;

            // Push this host's next turn out, so whatever the run does next
            // does not walk straight into the same wall.
            $this->lastRequestAt[$host] = microtime(true) + $retryAfter;
        }

        throw new RuntimeException($this->explain($response->status(), $url, $retryAfter));
    }

    /** `Retry-After` as seconds, whether it arrived as seconds or as a date. */
    private function retryAfterSeconds(Response $response): ?int
    {
        $header = $response->header('Retry-After');

        if ($header === null || $header === '') {
            return null;
        }

        if (is_numeric($header)) {
            return max(0, (int) $header);
        }

        $timestamp = strtotime($header);

        return $timestamp === false ? null : max(0, $timestamp - time());
    }

    /**
     * Headers sent with every request.
     *
     * The extra `headers` setting exists because a site that starts refusing
     * us is a configuration problem, not a code one — a Referer or a different
     * User-Agent has to be settable without a deploy.
     *
     * @return array<string, string>
     */
    public function headers(ScrapeSource $source): array
    {
        $extra = $source->setting('headers');

        // Nastavení zdroje je mapa řetězců, takže hlavičky se zapisují jako
        // JSON: {"Referer":"https://…"}. Nesmysl v tom poli nesmí shodit běh.
        if (is_string($extra) && trim($extra) !== '') {
            $decoded = json_decode($extra, true);
            $extra = is_array($decoded) ? $decoded : [];
        }

        $headers = [
            'User-Agent' => (string) $source->setting('user_agent'),
            'Accept' => 'text/html,application/xhtml+xml',
            'Accept-Language' => 'cs,en;q=0.8',
        ];

        // Vlastní cookies. Pomůžou u webů za přihlášením; u kontroly
        // prohlížeče typu Cloudflare ne — ta váže své cookie na adresu a
        // User-Agent, kterým ji vydala, takže zkopírovaná z jiného počítače
        // neplatí. Je lepší to říct rovnou než nechat někoho hodinu zkoušet.
        $cookies = $source->setting('cookies');

        if (is_string($cookies) && trim($cookies) !== '') {
            $headers['Cookie'] = trim($cookies);
        }

        return array_merge($headers, is_array($extra) ? array_map('strval', $extra) : []);
    }

    /**
     * Say what a failing status actually means.
     *
     * „HTTP 403" alone sent people looking for a bug in the selectors. A 403
     * on a page that opens fine in a browser is the site refusing this server,
     * and what to do about it is a different job entirely.
     */
    private function explain(int $status, string $url, ?int $retryAfter = null): string
    {
        $detail = match (true) {
            $status === 403 => 'Web nás odmítl. Stránka bývá dostupná z prohlížeče i odjinud, takže jde nejspíš o blokaci této IP nebo našeho User-Agentu — zkuste u zdroje nastavit jiný User-Agent, přidat hlavičku Referer, nebo stahovat z jiné adresy.',
            $status === 429 => 'Web nás odmítl kvůli rychlosti. Zvyšte u zdroje prodlevu mezi dotazy.',
            $status === 404 => 'Stránka na téhle adrese není. Zkontrolujte odkaz na výpis.',
            $status >= 500 => 'Chyba na straně webu, ne u nás. Zkuste to později.',
            default => null,
        };

        // Když web sám řekne, za jak dlouho to zkusit znovu, je to ta
        // nejužitečnější věc v celé hlášce.
        $wait = $retryAfter !== null
            ? " Web žádá počkat {$retryAfter} s."
            : '';

        return "HTTP {$status} pro {$url}" . ($detail ? ' — ' . $detail : '') . $wait;
    }

    /** Fetch binary content (images), under the same rate limit. */
    public function getBinary(ScrapeSource $source, string $url): string
    {
        $this->waitForTurn($source, $url);

        $response = $this->request($source)->get($url);

        if (! $response->successful()) {
            throw new RuntimeException('Obrázek: ' . $this->explain($response->status(), $url, $this->retryAfterSeconds($response)));
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
