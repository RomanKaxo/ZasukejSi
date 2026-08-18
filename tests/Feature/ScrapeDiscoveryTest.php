<?php

namespace Tests\Feature;

use App\Models\ScrapeItem;
use App\Models\ScrapeRun;
use App\Models\ScrapeSource;
use App\Services\Scraping\ScrapeRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Jak scraper hledá adresy profilů, když se nedají spočítat.
 *
 * Číslované stránkování je nejhorší možný předpoklad: platí jen tam, kde web
 * čísla stránek vůbec má. Sitemapa a odkaz „další stránka" jsou dvě cesty,
 * jak zprovoznit nový web bez hádání, a obojí musí fungovat samo.
 */
class ScrapeDiscoveryTest extends TestCase
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
                'conditional_requests' => false,
            ], $settings),
        ]);
    }

    private function sitemap(string $body): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . $body . '</urlset>';
    }

    public function test_a_sitemap_supplies_the_detail_addresses(): void
    {
        Http::fake([
            'https://example.test/sitemap.xml' => Http::response($this->sitemap(
                '<url><loc>https://example.test/p/1</loc></url>'
                . '<url><loc>https://example.test/p/2</loc></url>'
                . '<url><loc>https://example.test/o-nas</loc></url>'
            ), 200, ['Content-Type' => 'application/xml']),
            'https://example.test/p/*' => Http::response('<h1>Profil</h1>', 200, ['Content-Type' => 'text/html']),
        ]);

        $run = app(ScrapeRunner::class)->run($this->source([
            'discovery' => 'sitemap',
            // Bez tohohle by se do fronty dostala i stránka „o nás".
            'detail_url_pattern' => '#/p/\d+#',
        ]));

        $this->assertSame(ScrapeRun::STATUS_COMPLETED, $run->status);
        $this->assertSame(2, $run->items_found);
        $this->assertSame(2, ScrapeItem::count());
    }

    public function test_a_sitemap_index_is_followed(): void
    {
        Http::fake([
            'https://example.test/sitemap.xml' => Http::response(
                '<?xml version="1.0" encoding="UTF-8"?>'
                . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
                . '<sitemap><loc>https://example.test/sitemap-profily.xml</loc></sitemap>'
                . '</sitemapindex>',
                200,
                ['Content-Type' => 'application/xml'],
            ),
            'https://example.test/sitemap-profily.xml' => Http::response(
                $this->sitemap('<url><loc>https://example.test/p/7</loc></url>'),
                200,
                ['Content-Type' => 'application/xml'],
            ),
            'https://example.test/p/7' => Http::response('<h1>Profil</h1>', 200, ['Content-Type' => 'text/html']),
        ]);

        $run = app(ScrapeRunner::class)->run($this->source([
            'discovery' => 'sitemap',
            'detail_url_pattern' => '#/p/\d+#',
        ]));

        $this->assertSame(1, $run->items_found);
    }

    /** Ta úspora, kvůli které to celé je: noční běh se nemá ptát na celý web. */
    public function test_only_entries_changed_since_the_last_success_are_taken(): void
    {
        $source = $this->source([
            'discovery' => 'sitemap',
            'detail_url_pattern' => '#/p/\d+#',
        ]);

        $source->forceFill(['last_success_at' => now()->subDays(5)])->save();

        Http::fake([
            'https://example.test/sitemap.xml' => Http::response($this->sitemap(
                '<url><loc>https://example.test/p/1</loc><lastmod>' . now()->subDays(30)->toDateString() . '</lastmod></url>'
                . '<url><loc>https://example.test/p/2</loc><lastmod>' . now()->subDay()->toDateString() . '</lastmod></url>'
            ), 200, ['Content-Type' => 'application/xml']),
            'https://example.test/p/2' => Http::response('<h1>Profil</h1>', 200, ['Content-Type' => 'text/html']),
        ]);

        $run = app(ScrapeRunner::class)->run($source);

        $this->assertSame(1, $run->items_found);
        $this->assertSame('https://example.test/p/2', ScrapeItem::first()->source_url);
    }

    public function test_pagination_can_follow_the_next_link_instead_of_counting(): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">A</a>'
                . '<a rel="next" href="https://example.test/list/?cursor=abc">Další</a>',
                200,
                ['Content-Type' => 'text/html'],
            ),
            'https://example.test/list/?cursor=abc' => Http::response(
                '<a class="profile" href="https://example.test/p/2">B</a>',
                200,
                ['Content-Type' => 'text/html'],
            ),
            'https://example.test/p/*' => Http::response('<h1>Profil</h1>', 200, ['Content-Type' => 'text/html']),
        ]);

        $run = app(ScrapeRunner::class)->run(
            $this->source(['pagination_mode' => 'next_link']),
            ['pages' => 5],
        );

        // Pátá stránka neexistuje; běh má skončit na druhé, ne selhat.
        $this->assertSame(ScrapeRun::STATUS_COMPLETED, $run->status);
        $this->assertSame(2, $run->items_found);
        $this->assertStringContainsString('další stránku nenabízí', (string) $run->log);
    }

    /** Odkaz „další", který ukazuje sám na sebe, byl dřív nekonečná smyčka. */
    public function test_a_self_referencing_next_link_ends_the_walk(): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">A</a>'
                . '<a rel="next" href="https://example.test/list/">Další</a>',
                200,
                ['Content-Type' => 'text/html'],
            ),
            'https://example.test/p/1' => Http::response('<h1>Profil</h1>', 200, ['Content-Type' => 'text/html']),
        ]);

        $run = app(ScrapeRunner::class)->run(
            $this->source(['pagination_mode' => 'next_link']),
            ['pages' => 10],
        );

        $this->assertSame(1, $run->items_found);
    }
}
