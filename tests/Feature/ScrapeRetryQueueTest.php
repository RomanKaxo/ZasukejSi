<?php

namespace Tests\Feature;

use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Services\Scraping\ScrapeRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Co se stane s detailem, který se nepodařilo stáhnout.
 *
 * Dosud nezůstalo nic než číslo v počitadle a řádek v logu — adresa byla
 * pryč. Profil se vrátil, až když někdo prošel celý výpis znovu, a u běhů,
 * které se ptají jen na to, co se změnilo, klidně nikdy.
 */
class ScrapeRetryQueueTest extends TestCase
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

    private function listing(): string
    {
        return '<a class="profile" href="https://example.test/p/1">A</a>';
    }

    public function test_a_failed_detail_is_kept_with_a_time_to_try_again(): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response($this->listing(), 200, ['Content-Type' => 'text/html']),
            'https://example.test/p/1' => Http::response('Gone', 404),
        ]);

        $run = app(ScrapeRunner::class)->run($this->source(), ['pages' => 1]);

        $this->assertSame(1, $run->items_failed);

        $item = ScrapeItem::first();

        $this->assertNotNull($item, 'Selhaná adresa se musí uložit, jinak je ztracená.');
        $this->assertSame(ScrapeItem::STATUS_FAILED, $item->status);
        $this->assertSame('https://example.test/p/1', $item->source_url);
        $this->assertSame(1, $item->attempts);
        $this->assertNotNull($item->retry_after);
        $this->assertStringContainsString('404', (string) $item->error);
    }

    /** Další běh ji zkusí znovu — i když ji výpis vůbec nenabídne. */
    public function test_a_due_failure_is_retried_even_when_the_listing_no_longer_offers_it(): void
    {
        $source = $this->source();

        ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/p/9',
            'external_id' => '9',
            'status' => ScrapeItem::STATUS_FAILED,
            'attempts' => 1,
            'retry_after' => now()->subHour(),
        ]);

        Http::fake([
            'https://example.test/list/' => Http::response('', 200, ['Content-Type' => 'text/html']),
            'https://example.test/p/9' => Http::response('<h1>Profil</h1>', 200, ['Content-Type' => 'text/html']),
        ]);

        $run = app(ScrapeRunner::class)->run($source, ['pages' => 1]);

        $item = ScrapeItem::first();

        $this->assertSame(1, $run->items_found);
        $this->assertSame(ScrapeItem::STATUS_PENDING, $item->status);
        $this->assertSame(0, $item->attempts, 'Po úspěchu se počitadlo pokusů nuluje.');
        $this->assertNull($item->retry_after);
    }

    /** Co ještě nedozrálo, se netahá — jinak by odstup nic neznamenal. */
    public function test_a_failure_that_is_not_due_yet_is_left_alone(): void
    {
        $source = $this->source();

        ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/p/9',
            'external_id' => '9',
            'status' => ScrapeItem::STATUS_FAILED,
            'attempts' => 1,
            'retry_after' => now()->addHours(3),
        ]);

        Http::fake([
            'https://example.test/list/' => Http::response('', 200, ['Content-Type' => 'text/html']),
        ]);

        $run = app(ScrapeRunner::class)->run($source, ['pages' => 1]);

        $this->assertSame(0, $run->items_found);
        $this->assertSame(1, ScrapeItem::first()->attempts);
    }

    /** Odstup mezi pokusy roste, ať se web, kterému není dobře, nedorazí. */
    public function test_the_gap_between_attempts_grows(): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response($this->listing(), 200, ['Content-Type' => 'text/html']),
            'https://example.test/p/1' => Http::response('Gone', 404),
        ]);

        $source = $this->source();
        $runner = app(ScrapeRunner::class);

        $runner->run($source, ['pages' => 1]);
        $first = ScrapeItem::first()->retry_after;

        // Ať je hned na řadě, aby druhý pokus proběhl v tomhle testu.
        ScrapeItem::first()->forceFill(['retry_after' => now()->subMinute()])->save();

        $runner->run($source->fresh(), ['pages' => 1]);
        $item = ScrapeItem::first();

        $this->assertSame(2, $item->attempts);
        $this->assertTrue(
            $item->retry_after->greaterThan($first),
            'Druhý pokus musí být dál než první.',
        );
    }

    public function test_after_enough_attempts_the_scraper_stops_on_its_own(): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response($this->listing(), 200, ['Content-Type' => 'text/html']),
            'https://example.test/p/1' => Http::response('Gone', 404),
        ]);

        $source = $this->source(['max_attempts' => 2]);
        $runner = app(ScrapeRunner::class);

        $runner->run($source, ['pages' => 1]);
        ScrapeItem::first()->forceFill(['retry_after' => now()->subMinute()])->save();
        $runner->run($source->fresh(), ['pages' => 1]);

        $item = ScrapeItem::first();

        $this->assertSame(2, $item->attempts);
        $this->assertNull($item->retry_after, 'Po vyčerpání pokusů se čekat na nic nemá.');
        $this->assertSame(0, ScrapeItem::query()->dueForRetry($source->id)->count());
    }

    /** Zkušební běh nesmí táhnout celou frontu ani zakládat záznamy. */
    public function test_a_dry_run_neither_records_nor_retries(): void
    {
        $source = $this->source();

        ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/p/9',
            'external_id' => '9',
            'status' => ScrapeItem::STATUS_FAILED,
            'attempts' => 1,
            'retry_after' => now()->subHour(),
        ]);

        Http::fake([
            'https://example.test/list/' => Http::response($this->listing(), 200, ['Content-Type' => 'text/html']),
            'https://example.test/p/1' => Http::response('Gone', 404),
        ]);

        $run = app(ScrapeRunner::class)->run($source, ['pages' => 1, 'dry_run' => true]);

        $this->assertSame(1, $run->items_found, 'Fronta se do zkušebního běhu tahat nemá.');
        $this->assertSame(1, ScrapeItem::count());
        $this->assertSame(1, ScrapeItem::first()->attempts);
    }

    /** Hotová položka si podrží svůj verdikt, i když stránka přestane odpovídat. */
    public function test_an_imported_item_is_not_demoted_by_a_later_failure(): void
    {
        $source = $this->source();

        ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/p/1',
            'external_id' => '1',
            'status' => ScrapeItem::STATUS_IMPORTED,
        ]);

        Http::fake([
            'https://example.test/list/' => Http::response($this->listing(), 200, ['Content-Type' => 'text/html']),
            'https://example.test/p/1' => Http::response('Gone', 404),
        ]);

        app(ScrapeRunner::class)->run($source, ['pages' => 1]);

        $item = ScrapeItem::first();

        $this->assertSame(ScrapeItem::STATUS_IMPORTED, $item->status);
        $this->assertSame(1, $item->attempts);
    }
}
