<?php

namespace Tests\Feature;

use App\Models\ScrapeItem;
use App\Models\ScrapeRun;
use App\Models\ScrapeSource;
use App\Models\ScrapeUrlCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Úklid účetnictví scraperu.
 *
 * Mezipaměť adres i historie běhů rostou donekonečna a ani jedno nejsou ničí
 * data. Sklizeň — položky a profily — se nemaže nikdy; to je rozhodnutí, ne
 * údržba.
 */
class ScrapePruneCommandTest extends TestCase
{
    use RefreshDatabase;

    private function source(): ScrapeSource
    {
        return ScrapeSource::create([
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
        ]);
    }

    private function cached(ScrapeSource $source, string $url, int $daysAgo): void
    {
        ScrapeUrlCache::create([
            'scrape_source_id' => $source->id,
            'url_hash' => ScrapeUrlCache::hashFor($url),
            'url' => $url,
            'content_hash' => sha1($url),
            'fetched_at' => now()->subDays($daysAgo),
        ]);
    }

    private function makeRun(ScrapeSource $source, ?int $finishedDaysAgo): ScrapeRun
    {
        return ScrapeRun::create([
            'scrape_source_id' => $source->id,
            'status' => $finishedDaysAgo === null ? ScrapeRun::STATUS_RUNNING : ScrapeRun::STATUS_COMPLETED,
            'started_at' => now()->subDays($finishedDaysAgo ?? 0),
            'finished_at' => $finishedDaysAgo === null ? null : now()->subDays($finishedDaysAgo),
        ]);
    }

    public function test_old_cache_rows_go_and_recent_ones_stay(): void
    {
        $source = $this->source();

        $this->cached($source, 'https://example.test/stare', 100);
        $this->cached($source, 'https://example.test/nove', 3);

        $this->artisan('scrape:prune')->assertSuccessful();

        $this->assertSame(1, ScrapeUrlCache::count());
        $this->assertSame('https://example.test/nove', ScrapeUrlCache::first()->url);
    }

    public function test_old_finished_runs_go(): void
    {
        $source = $this->source();

        $this->makeRun($source, 200);
        $this->makeRun($source, 5);

        $this->artisan('scrape:prune')->assertSuccessful();

        $this->assertSame(1, ScrapeRun::count());
    }

    /** Běh, který právě běží, se nesmí smazat, ať je jakkoli starý. */
    public function test_an_unfinished_run_is_left_alone(): void
    {
        $source = $this->source();

        $running = $this->makeRun($source, null);
        $running->forceFill(['started_at' => now()->subDays(300)])->save();

        $this->artisan('scrape:prune')->assertSuccessful();

        $this->assertSame(1, ScrapeRun::count());
    }

    /** Sklizeň se nemaže. Nikdy. */
    public function test_scraped_items_are_never_touched(): void
    {
        $source = $this->source();
        $run = $this->makeRun($source, 200);

        ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'scrape_run_id' => $run->id,
            'source_url' => 'https://example.test/p/1',
            'external_id' => '1',
            'status' => ScrapeItem::STATUS_PENDING,
        ]);

        $this->artisan('scrape:prune')->assertSuccessful();

        $this->assertSame(1, ScrapeItem::count());
        $this->assertNull(ScrapeItem::first()->scrape_run_id, 'Běh zmizel, položka zůstala.');
    }

    public function test_a_dry_run_deletes_nothing(): void
    {
        $source = $this->source();

        $this->cached($source, 'https://example.test/stare', 100);

        $this->artisan('scrape:prune', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame(1, ScrapeUrlCache::count());
    }
}
