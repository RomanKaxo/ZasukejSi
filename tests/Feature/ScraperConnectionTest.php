<?php

namespace Tests\Feature;

use App\Models\ScrapeSource;
use App\Services\Scraping\HttpFetcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

/**
 * Co scraper udělá, když ho web odmítne.
 *
 * „HTTP 403" bez dalšího posílalo člověka hledat chybu v selektorech. 403 na
 * stránce, která se v prohlížeči otevře, je odmítnutí serveru — a to se řeší
 * úplně jinak.
 */
class ScraperConnectionTest extends TestCase
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
                'timeout' => 5,
                'respect_robots' => false,
            ], $settings),
        ]);
    }

    public function test_a_403_says_it_is_a_refusal_not_a_broken_selector(): void
    {
        Http::fake(['*' => Http::response('', 403)]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/403.*odmítl/su');

        app(HttpFetcher::class)->get($this->source(), 'https://example.test/vypis/');
    }

    public function test_a_429_points_at_the_crawl_delay(): void
    {
        Http::fake(['*' => Http::response('', 429)]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/429.*prodlevu/su');

        app(HttpFetcher::class)->get($this->source(), 'https://example.test/vypis/');
    }

    public function test_a_404_points_at_the_address(): void
    {
        Http::fake(['*' => Http::response('', 404)]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/404.*adrese/su');

        app(HttpFetcher::class)->get($this->source(), 'https://example.test/vypis/');
    }

    public function test_the_user_agent_can_be_changed_per_source(): void
    {
        $source = $this->source(['user_agent' => 'Mozilla/5.0 (test)']);

        $this->assertSame('Mozilla/5.0 (test)', app(HttpFetcher::class)->headers($source)['User-Agent']);
    }

    /** Nastavení zdroje je mapa řetězců, hlavičky se proto píšou jako JSON. */
    public function test_extra_headers_can_be_given_as_json(): void
    {
        $source = $this->source(['headers' => '{"Referer":"https://www.example.com/"}']);

        $headers = app(HttpFetcher::class)->headers($source);

        $this->assertSame('https://www.example.com/', $headers['Referer']);
        // Výchozí hlavičky zůstávají.
        $this->assertArrayHasKey('Accept-Language', $headers);
    }

    public function test_nonsense_in_the_headers_setting_does_not_break_the_run(): void
    {
        $source = $this->source(['headers' => 'tohle není JSON']);

        $headers = app(HttpFetcher::class)->headers($source);

        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertArrayNotHasKey('tohle není JSON', $headers);
    }

    public function test_a_successful_fetch_returns_the_body(): void
    {
        Http::fake(['*' => Http::response('<html><body>ok</body></html>', 200)]);

        $this->assertStringContainsString('ok', app(HttpFetcher::class)->get($this->source(), 'https://example.test/'));
    }
}
