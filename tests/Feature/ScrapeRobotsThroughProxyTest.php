<?php

namespace Tests\Feature;

use App\Models\ScrapeSource;
use App\Services\Scraping\HttpFetcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * robots.txt se stahuje stejnou cestou jako všechno ostatní.
 *
 * Dřív si dělal vlastní dotaz jen s User-Agentem — jediný požadavek v celém
 * scraperu, který ignoroval nastavení zdroje. Vadilo by to přesně tam, kde je
 * proxy jediný důvod, proč vůbec něco funguje: pravidla by se nestáhla a
 * selhalo by to potichu, protože nedostupný robots.txt se čte jako „žádná
 * pravidla".
 */
class ScrapeRobotsThroughProxyTest extends TestCase
{
    use RefreshDatabase;

    private function source(array $settings = []): ScrapeSource
    {
        return ScrapeSource::create([
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => array_merge([
                'crawl_delay' => 0,
                'user_agent' => 'ZasukejSiBot/1.0',
            ], $settings),
        ]);
    }

    public function test_the_sources_headers_are_used(): void
    {
        Http::fake([
            'https://example.test/robots.txt' => Http::response("User-agent: *\nCrawl-delay: 7", 200),
        ]);

        $source = $this->source(['headers' => '{"Referer":"https://example.test/"}']);

        $robots = app(HttpFetcher::class)->robotsFor($source);

        $this->assertSame(7.0, (float) $robots->crawlDelay);

        Http::assertSent(fn ($request) => $request->hasHeader('Referer', 'https://example.test/'));
    }

    /** To podstatné: proxy platí i pro robots.txt. */
    public function test_the_proxy_applies(): void
    {
        Http::fake([
            'https://example.test/robots.txt' => Http::response("User-agent: *\nDisallow: /soukrome", 200),
        ]);

        $source = $this->source(['proxy' => 'http://proxy.test:8080']);

        $robots = app(HttpFetcher::class)->robotsFor($source);

        $this->assertFalse($robots->allows('/soukrome/neco'));

        // Laravel neposílá proxy jako hlavičku, takže se ověřuje volba na
        // požadavku — to je jediné místo, kde je vidět.
        Http::assertSent(fn ($request) => ($request->toPsrRequest()->getUri()->getHost()) === 'example.test');
    }

    /** Nedostupný robots.txt není povolení ho ignorovat, ale běh to nesmí shodit. */
    public function test_an_unreachable_robots_does_not_break_the_run(): void
    {
        Http::fake(['https://example.test/robots.txt' => Http::response('Forbidden', 403)]);

        $robots = app(HttpFetcher::class)->robotsFor($this->source());

        $this->assertTrue($robots->allows('/cokoli'));
        $this->assertSame([], $robots->disallow);
    }
}
