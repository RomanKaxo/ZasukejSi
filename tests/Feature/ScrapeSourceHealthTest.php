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
 * Co se stane, když zdroj přestane fungovat, a co když se zase rozběhne.
 *
 * Zdroj, který nás začal odmítat, si dřív podržel své místo v plánu a ptal se
 * dál každých pár hodin donekonečna. Nikomu se to neřeklo a web dostával bota,
 * který ho stejně neuměl přečíst.
 */
class ScrapeSourceHealthTest extends TestCase
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
            'schedule_hours' => 6,
            'settings' => array_merge([
                'crawl_delay' => 0,
                'listing_path' => '/list',
                'detail_link_selector' => 'a.profile',
                'respect_robots' => false,
                'conditional_requests' => false,
            ], $settings),
        ]);
    }

    private function failing(): void
    {
        Http::fake([
            'https://example.test/*' => Http::response('Forbidden', 403),
        ]);
    }

    public function test_a_source_is_paused_after_repeated_failures(): void
    {
        $this->failing();

        $source = $this->source(['failure_threshold' => 2]);
        $runner = app(ScrapeRunner::class);

        $runner->run($source);
        $this->assertFalse($source->fresh()->isPaused(), 'Jedno selhání pauzu spustit nesmí.');

        $runner->run($source->fresh());

        $source->refresh();
        $this->assertTrue($source->isPaused());
        $this->assertSame(2, $source->consecutive_failures);
        $this->assertStringContainsString('403', (string) $source->paused_reason);
    }

    /** Pozastavený zdroj vypadne z plánu, ale nesmí se smazat ani vypnout. */
    public function test_a_paused_source_drops_out_of_the_schedule(): void
    {
        $this->failing();

        $source = $this->source(['failure_threshold' => 1]);
        app(ScrapeRunner::class)->run($source);

        $source->refresh();

        $this->assertTrue($source->is_enabled);
        $this->assertFalse($source->isScheduled());
        $this->assertSame(0, ScrapeSource::query()->due()->count());
    }

    public function test_auto_pause_can_be_switched_off(): void
    {
        $this->failing();

        $source = $this->source(['auto_pause' => false, 'failure_threshold' => 1]);
        app(ScrapeRunner::class)->run($source);

        $source->refresh();

        $this->assertFalse($source->isPaused());
        $this->assertSame(1, $source->consecutive_failures);
    }

    public function test_a_successful_run_clears_the_pause_and_stamps_the_success(): void
    {
        $source = $this->source(['failure_threshold' => 1]);

        // Jedna sada podvržených odpovědí na oba běhy: Http::fake() se slučuje
        // a první zaregistrovaný vzor vyhrává, takže druhé volání by tu 403
        // nepřebilo.
        Http::fake([
            'https://example.test/list/' => Http::sequence()
                ->push('Forbidden', 403)
                ->push('<a class="profile" href="https://example.test/p/1">A</a>', 200, ['Content-Type' => 'text/html']),
            'https://example.test/p/1' => Http::response('<h1>Profil</h1>', 200, ['Content-Type' => 'text/html']),
        ]);

        app(ScrapeRunner::class)->run($source, ['pages' => 1]);
        $this->assertTrue($source->fresh()->isPaused());

        $run = app(ScrapeRunner::class)->run($source->fresh(), ['pages' => 1]);

        $source->refresh();

        $this->assertSame(ScrapeRun::STATUS_COMPLETED, $run->status);
        $this->assertFalse($source->isPaused());
        $this->assertSame(0, $source->consecutive_failures);
        $this->assertNotNull($source->last_success_at);
    }

    /** Zkušební běh je zkoušení, ne provoz — funkční zdroj pozastavit nesmí. */
    public function test_a_dry_run_never_touches_the_health(): void
    {
        $this->failing();

        $source = $this->source(['failure_threshold' => 1]);
        app(ScrapeRunner::class)->run($source, ['dry_run' => true]);

        $source->refresh();

        $this->assertFalse($source->isPaused());
        $this->assertSame(0, $source->consecutive_failures);
    }

    /**
     * Stránka, o které web sám řekne, že se nezměnila, se nemá znovu
     * zpracovávat — a hlavně se u ní nemají znovu stahovat fotky.
     */
    public function test_an_unchanged_detail_page_is_not_processed_again(): void
    {
        $source = $this->source(['conditional_requests' => true]);

        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">A</a>',
                200,
                ['Content-Type' => 'text/html'],
            ),
            'https://example.test/p/1' => Http::sequence()
                ->push('<h1>Profil</h1>', 200, ['Content-Type' => 'text/html', 'ETag' => '"abc"'])
                ->push('', 304),
        ]);

        $runner = app(ScrapeRunner::class);

        $first = $runner->run($source, ['pages' => 1]);
        $this->assertSame(1, $first->items_new);

        $second = $runner->run($source->fresh(), ['pages' => 1]);

        $this->assertSame(0, $second->items_new, (string) $second->log);
        $this->assertSame(0, $second->items_updated, (string) $second->log);
        $this->assertStringContainsString('beze změny', (string) $second->log);
        $this->assertSame(1, ScrapeItem::count());
    }

    /**
     * Weby, které ETag ani Last-Modified neposílají, jsou většina. Tam nás
     * zachrání až porovnání obsahu — server ho pošle, ale dál se s ním nic
     * nedělá.
     */
    public function test_identical_content_without_an_etag_also_counts_as_unchanged(): void
    {
        $source = $this->source(['conditional_requests' => true]);

        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">A</a>',
                200,
                ['Content-Type' => 'text/html'],
            ),
            'https://example.test/p/1' => Http::response('<h1>Profil</h1>', 200, ['Content-Type' => 'text/html']),
        ]);

        $runner = app(ScrapeRunner::class);
        $runner->run($source, ['pages' => 1]);

        $second = $runner->run($source->fresh(), ['pages' => 1]);

        $this->assertSame(0, $second->items_updated, (string) $second->log);
        $this->assertStringContainsString('beze změny', (string) $second->log);
    }
}
