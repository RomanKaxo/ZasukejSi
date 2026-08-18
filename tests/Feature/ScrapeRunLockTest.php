<?php

namespace Tests\Feature;

use App\Models\ScrapeItem;
use App\Models\ScrapeRun;
use App\Models\ScrapeSource;
use App\Services\Scraping\ScrapeRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Dva běhy téhož zdroje najednou.
 *
 * Cron a kliknutí v administraci se snadno potkají a zátěž cizího webu se tím
 * zdvojnásobí — přesně to, čemu se prodleva mezi dotazy snaží zabránit.
 * Prodleva se totiž počítá zvlášť v každém procesu, takže sama o sobě proti
 * souběhu nepomůže.
 */
class ScrapeRunLockTest extends TestCase
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
            ], $settings),
        ]);

        $source->fieldMaps()->create([
            'target_field' => 'display_name',
            'selector' => 'h1',
            'extract' => 'text',
            'sort_order' => 0,
        ]);

        return $source;
    }

    private function fake(int $profiles = 3): void
    {
        $links = '';

        for ($i = 1; $i <= $profiles; $i++) {
            $links .= '<a class="profile" href="https://example.test/p/' . $i . '">P</a>';
        }

        Http::fake([
            'https://example.test/list/' => Http::response($links, 200, ['Content-Type' => 'text/html']),
            'https://example.test/p/*' => Http::response('<h1>Kristýna</h1>', 200, ['Content-Type' => 'text/html']),
        ]);
    }

    public function test_a_second_run_of_the_same_source_is_refused(): void
    {
        $this->fake();

        $source = $this->source();

        // Zámek drží jiný proces.
        $lock = Cache::lock('scrape:source:' . $source->id, 60);
        $this->assertTrue($lock->get());

        $run = app(ScrapeRunner::class)->run($source, ['pages' => 1]);

        $this->assertSame(ScrapeRun::STATUS_FAILED, $run->status);
        $this->assertStringContainsString('právě běží', (string) $run->error);
        $this->assertSame(0, ScrapeItem::count());

        $lock->release();
    }

    /** Po doběhnutí je zdroj zase volný — jinak by ho jeden běh zamkl navždy. */
    public function test_the_lock_is_released_afterwards(): void
    {
        $this->fake();

        $source = $this->source();
        $runner = app(ScrapeRunner::class);

        $first = $runner->run($source, ['pages' => 1]);
        $this->assertSame(ScrapeRun::STATUS_COMPLETED, $first->status);

        $second = $runner->run($source->fresh(), ['pages' => 1]);
        $this->assertSame(ScrapeRun::STATUS_COMPLETED, $second->status);
    }

    /** I když běh selže, zámek nesmí zůstat viset. */
    public function test_a_failed_run_still_releases_the_lock(): void
    {
        Http::fake(['https://example.test/*' => Http::response('Forbidden', 403)]);

        $source = $this->source();
        $runner = app(ScrapeRunner::class);

        $this->assertSame(ScrapeRun::STATUS_FAILED, $runner->run($source, ['pages' => 1])->status);

        // Druhý běh narazí zase na 403 — Http::fake() se slučuje a první vzor
        // vyhrává. O co jde: nesmí selhat na zámku.
        $second = $runner->run($source->fresh(), ['pages' => 1]);

        $this->assertStringNotContainsString('právě běží', (string) $second->error);
    }

    /**
     * Chybné stránkování dokáže vyrobit nekonečný seznam adres. Bez stropu by
     * scraper poslušně ťukal na cizí server, dokud ho někdo nezastaví.
     */
    public function test_a_run_stops_at_the_request_ceiling(): void
    {
        $this->fake(10);

        // Jeden požadavek padne na výpis, zbytek na detaily.
        $run = app(ScrapeRunner::class)->run($this->source(['max_requests' => 4]), ['pages' => 1]);

        $this->assertSame(ScrapeRun::STATUS_COMPLETED, $run->status);
        $this->assertLessThan(10, ScrapeItem::count());
        $this->assertStringContainsString('stropu', (string) $run->error);
    }

    public function test_without_a_ceiling_everything_is_fetched(): void
    {
        $this->fake(10);

        app(ScrapeRunner::class)->run($this->source(), ['pages' => 1]);

        $this->assertSame(10, ScrapeItem::count());
    }
}
