<?php

namespace Tests\Feature;

use App\Models\ScrapeItem;
use App\Models\ScrapeRun;
use App\Models\ScrapeSource;
use App\Services\Scraping\PageSnapshots;
use App\Services\Scraping\ScrapeRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Web se předělal a všechny selektory přestaly platit naráz.
 *
 * Běh, který na to naběhne, není sklizeň — je to sto profilů označených za
 * rozbité jedním po druhém scraperem, který stránku prostě neumí přečíst.
 * Prvních pár stačí k rozpoznání; zbývajících pětadevadesát jsou zbytečné
 * dotazy na cizí server.
 */
class ScrapeRedesignGuardTest extends TestCase
{
    use RefreshDatabase;

    private function source(array $settings = []): ScrapeSource
    {
        $source = ScrapeSource::create([
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
                'redesign_min_items' => 3,
                'redesign_ratio' => 0.8,
            ], $settings),
        ]);

        $source->fieldMaps()->create([
            'target_field' => 'display_name',
            'selector' => 'h1.jmeno',
            'extract' => 'text',
            'is_required' => true,
            'sort_order' => 0,
        ]);

        return $source;
    }

    /** Výpis s deseti profily a detaily, které po předělání nevrací nic. */
    private function fakeRedesign(int $count = 10): void
    {
        $links = '';

        for ($i = 1; $i <= $count; $i++) {
            $links .= '<a class="profile" href="https://example.test/p/' . $i . '">P</a>';
        }

        Http::fake([
            'https://example.test/list/' => Http::response($links, 200, ['Content-Type' => 'text/html']),
            // Nová šablona: nadpis je jinde, selektor nesedí na ničem.
            'https://example.test/p/*' => Http::response(
                '<div class="new-title">Kristýna</div>',
                200,
                ['Content-Type' => 'text/html'],
            ),
        ]);
    }

    public function test_a_run_that_starts_reading_nothing_stops(): void
    {
        $this->fakeRedesign();

        $run = app(ScrapeRunner::class)->run($this->source(), ['pages' => 1]);

        $this->assertSame(ScrapeRun::STATUS_FAILED, $run->status);
        $this->assertStringContainsString('předělal', (string) $run->error);

        // A hlavně: nedojelo to do konce. Zbytek adres se ani nestahoval.
        $this->assertLessThan(10, ScrapeItem::count());
    }

    /** Pár neúplných profilů je normální provoz, ne poplach. */
    public function test_a_few_incomplete_profiles_do_not_stop_the_run(): void
    {
        $links = '';

        for ($i = 1; $i <= 10; $i++) {
            $links .= '<a class="profile" href="https://example.test/p/' . $i . '">P</a>';
        }

        Http::fake([
            'https://example.test/list/' => Http::response($links, 200, ['Content-Type' => 'text/html']),
            'https://example.test/p/1' => Http::response('<div>bez jména</div>', 200, ['Content-Type' => 'text/html']),
            'https://example.test/p/2' => Http::response('<div>bez jména</div>', 200, ['Content-Type' => 'text/html']),
            'https://example.test/p/*' => Http::response('<h1 class="jmeno">Kristýna</h1>', 200, ['Content-Type' => 'text/html']),
        ]);

        $run = app(ScrapeRunner::class)->run($this->source(), ['pages' => 1]);

        $this->assertSame(ScrapeRun::STATUS_COMPLETED, $run->status);
        $this->assertSame(10, ScrapeItem::count());
    }

    public function test_the_guard_can_be_switched_off(): void
    {
        $this->fakeRedesign();

        $run = app(ScrapeRunner::class)->run(
            $this->source(['redesign_guard' => false]),
            ['pages' => 1],
        );

        $this->assertSame(ScrapeRun::STATUS_COMPLETED, $run->status);
        $this->assertSame(10, ScrapeItem::count());
    }

    /**
     * To nejdůležitější: existující data se nepřepíšou prázdnem. Přepsání je
     * jediný následek, který se nedá vzít zpět.
     */
    public function test_good_values_are_not_replaced_with_emptiness(): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">P</a>',
                200,
                ['Content-Type' => 'text/html'],
            ),
            'https://example.test/p/1' => Http::sequence()
                ->push('<h1 class="jmeno">Kristýna</h1>', 200, ['Content-Type' => 'text/html'])
                ->push('<div class="new-title">Kristýna</div>', 200, ['Content-Type' => 'text/html']),
        ]);

        $source = $this->source();
        $runner = app(ScrapeRunner::class);

        $runner->run($source, ['pages' => 1]);
        $this->assertSame('Kristýna', ScrapeItem::first()->normalized['display_name']);

        $runner->run($source->fresh(), ['pages' => 1]);

        $item = ScrapeItem::first();

        $this->assertSame('Kristýna', $item->normalized['display_name'], 'Data musí zůstat.');
        $this->assertSame(1, $item->attempts, 'A zapsat se to má jako neúspěšný pokus.');
        $this->assertStringContainsString('povinných polí', (string) $item->error);
    }

    /** Uložená stránka je to, co viděl extraktor — proto se dá zkoušet zpětně. */
    public function test_the_page_is_kept_for_later(): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">P</a>',
                200,
                ['Content-Type' => 'text/html'],
            ),
            'https://example.test/p/1' => Http::response(
                '<h1 class="jmeno">Kristýna</h1>',
                200,
                ['Content-Type' => 'text/html'],
            ),
        ]);

        app(ScrapeRunner::class)->run($this->source(), ['pages' => 1]);

        $snapshots = app(PageSnapshots::class);
        $item = ScrapeItem::first();

        $this->assertTrue($snapshots->has($item));
        $this->assertStringContainsString('Kristýna', (string) $snapshots->get($item));

        $snapshots->forget($item);
    }

    public function test_snapshots_can_be_switched_off(): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">P</a>',
                200,
                ['Content-Type' => 'text/html'],
            ),
            'https://example.test/p/1' => Http::response(
                '<h1 class="jmeno">Kristýna</h1>',
                200,
                ['Content-Type' => 'text/html'],
            ),
        ]);

        app(ScrapeRunner::class)->run($this->source(['keep_snapshot' => false]), ['pages' => 1]);

        $this->assertFalse(app(PageSnapshots::class)->has(ScrapeItem::first()));
    }
}
