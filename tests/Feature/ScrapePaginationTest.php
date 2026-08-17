<?php

namespace Tests\Feature;

use App\Models\ScrapeRun;
use App\Models\ScrapeSource;
use App\Services\Scraping\ScrapeRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * A listing page that errors is not the same as one that is empty, but the
 * run ended on both and reported success. A wrong pagination setting then
 * looked like "the source only has one page" — which is how a harvest of Brno
 * stopped at 23 profiles instead of walking the whole listing.
 */
class ScrapePaginationTest extends TestCase
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
                'listing_path' => '/list',
                'detail_link_selector' => 'a.profile',
                'respect_robots' => false,
            ], $settings),
        ]);
    }

    public function test_a_broken_pagination_setting_is_reported_on_the_run(): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">A</a>',
                200,
                ['Content-Type' => 'text/html']
            ),
            // What the real site did: the path-style second page 404s.
            'https://example.test/list/2/' => Http::response('nope', 404),
            'https://example.test/p/1' => Http::response('<h1>A</h1>', 200, ['Content-Type' => 'text/html']),
        ]);

        $run = app(ScrapeRunner::class)->run(
            $this->source(['pagination_pattern' => '/{page}/']),
            ['pages' => 3]
        );

        // The first page still yielded its profile, so this is not a failure —
        // but the operator has to be able to see why it stopped.
        $this->assertSame(ScrapeRun::STATUS_COMPLETED, $run->status);
        $this->assertNotNull($run->error);
        $this->assertStringContainsString('Stránkování se zastavilo', $run->error);
        $this->assertStringContainsString('pagination_param', $run->error);
    }

    public function test_the_query_parameter_style_walks_every_page(): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">A</a>',
                200,
                ['Content-Type' => 'text/html']
            ),
            'https://example.test/list/?page=2' => Http::response(
                '<a class="profile" href="https://example.test/p/2">B</a>',
                200,
                ['Content-Type' => 'text/html']
            ),
            // An empty page is the end of the listing, not an error.
            'https://example.test/list/?page=3' => Http::response('', 200, ['Content-Type' => 'text/html']),
            'https://example.test/p/*' => Http::response('<h1>X</h1>', 200, ['Content-Type' => 'text/html']),
        ]);

        $run = app(ScrapeRunner::class)->run($this->source(), ['pages' => 5]);

        $this->assertSame(2, $run->items_found);
        $this->assertNull($run->error);
    }

    /**
     * Some listings ignore the pagination parameter and keep serving page one.
     * eurogirlsescort.cz does exactly that for a city with a single page, so a
     * run asked for twelve pages politely re-fetched the same profiles twelve
     * times over — a minute of crawl delay for nothing.
     */
    public function test_a_page_that_repeats_the_first_one_ends_the_walk(): void
    {
        $samePage = '<a class="profile" href="https://example.test/p/1">A</a>';

        Http::fake([
            'https://example.test/list/' => Http::response($samePage, 200, ['Content-Type' => 'text/html']),
            'https://example.test/list/*' => Http::response($samePage, 200, ['Content-Type' => 'text/html']),
            'https://example.test/p/1' => Http::response('<h1>A</h1>', 200, ['Content-Type' => 'text/html']),
        ]);

        $run = app(ScrapeRunner::class)->run($this->source(), ['pages' => 12]);

        $this->assertSame(1, $run->items_found);
        // Page one, then one repeat that ends it — not twelve.
        $this->assertSame(2, $run->pages_fetched);
        $this->assertStringContainsString('nepřinesla nic nového', (string) $run->log);
    }

    public function test_the_duration_is_never_negative(): void
    {
        $run = ScrapeRun::create([
            'scrape_source_id' => $this->source()->id,
            'status' => ScrapeRun::STATUS_COMPLETED,
            'started_at' => now()->subSeconds(171),
            'finished_at' => now(),
            'pages_fetched' => 1,
            'items_found' => 0,
            'items_new' => 0,
            'items_updated' => 0,
            'items_failed' => 0,
        ]);

        // The admin showed a three-minute harvest as "-171 s".
        $this->assertSame(171, $run->durationSeconds());
    }

    public function test_a_first_page_that_fails_is_not_blamed_on_pagination(): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response('down', 500),
        ]);

        $run = app(ScrapeRunner::class)->run($this->source(), ['pages' => 3]);

        // Nothing to do with pagination — the source itself did not answer.
        $this->assertSame(0, $run->items_found);
        $this->assertStringNotContainsString('Stránkování se zastavilo', (string) $run->error);
    }
}
