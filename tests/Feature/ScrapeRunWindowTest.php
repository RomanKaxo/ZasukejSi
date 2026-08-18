<?php

namespace Tests\Feature;

use App\Models\ScrapeSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Kdy se smí spouštět plánovaný běh.
 *
 * Stahovat cizí web v jeho nejrušnější hodinu je nezdvořilé a zároveň
 * nejjistější cesta k tomu, aby si nás všimli a zablokovali. V noci je to
 * nestojí nic.
 */
class ScrapeRunWindowTest extends TestCase
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
            'settings' => $settings,
        ]);
    }

    public function test_without_a_window_a_source_may_run_at_any_hour(): void
    {
        $source = $this->source();

        $this->assertTrue($source->isWithinWindow(Carbon::parse('2026-08-19 03:00')));
        $this->assertTrue($source->isWithinWindow(Carbon::parse('2026-08-19 14:00')));
    }

    public function test_an_ordinary_window_holds(): void
    {
        $source = $this->source(['run_window_from' => 2, 'run_window_to' => 6]);

        $this->assertFalse($source->isWithinWindow(Carbon::parse('2026-08-19 01:59')));
        $this->assertTrue($source->isWithinWindow(Carbon::parse('2026-08-19 02:00')));
        $this->assertTrue($source->isWithinWindow(Carbon::parse('2026-08-19 05:59')));
        $this->assertFalse($source->isWithinWindow(Carbon::parse('2026-08-19 06:00')));
    }

    /** 22 až 6 je to nejběžnější nastavení, takže přechod přes půlnoc musí sedět. */
    public function test_a_window_may_cross_midnight(): void
    {
        $source = $this->source(['run_window_from' => 22, 'run_window_to' => 6]);

        $this->assertTrue($source->isWithinWindow(Carbon::parse('2026-08-19 23:30')));
        $this->assertTrue($source->isWithinWindow(Carbon::parse('2026-08-19 01:00')));
        $this->assertFalse($source->isWithinWindow(Carbon::parse('2026-08-19 12:00')));
    }

    public function test_weekdays_can_be_limited(): void
    {
        // Jen víkend.
        $source = $this->source(['run_days' => [6, 7]]);

        $this->assertTrue($source->isWithinWindow(Carbon::parse('2026-08-22 10:00')), 'sobota');
        $this->assertTrue($source->isWithinWindow(Carbon::parse('2026-08-23 10:00')), 'neděle');
        $this->assertFalse($source->isWithinWindow(Carbon::parse('2026-08-19 10:00')), 'středa');
    }

    /** Všech sedm dnů je totéž co žádné omezení. */
    public function test_every_day_selected_is_no_restriction(): void
    {
        $source = $this->source(['run_days' => [1, 2, 3, 4, 5, 6, 7]]);

        $this->assertSame([], $source->windowDays());
        $this->assertTrue($source->isWithinWindow(Carbon::parse('2026-08-19 10:00')));
    }

    public function test_a_source_outside_its_window_is_not_due(): void
    {
        Carbon::setTestNow('2026-08-19 12:00');

        $source = $this->source(['run_window_from' => 2, 'run_window_to' => 6]);

        $this->assertTrue($source->isScheduled());
        $this->assertFalse($source->isDue());

        Carbon::setTestNow('2026-08-19 03:00');

        $this->assertTrue($source->fresh()->isDue());

        Carbon::setTestNow();
    }

    public function test_the_command_says_when_the_window_opens_again(): void
    {
        Carbon::setTestNow('2026-08-19 12:00');

        $this->source(['run_window_from' => 2, 'run_window_to' => 6]);

        $this->artisan('scrape:due')
            ->expectsOutputToContain('čeká na své okno')
            ->assertSuccessful();

        Carbon::setTestNow();
    }

    /** Ruční běh se oknem neřídí — když u toho někdo sedí, odpověď je zjevná. */
    public function test_force_ignores_the_window(): void
    {
        Carbon::setTestNow('2026-08-19 12:00');

        $this->source(['run_window_from' => 2, 'run_window_to' => 6]);

        $this->artisan('scrape:due', ['--force' => true, '--source' => 'test'])
            ->doesntExpectOutputToContain('čeká na své okno');

        Carbon::setTestNow();
    }

    public function test_the_next_opening_is_reported(): void
    {
        Carbon::setTestNow('2026-08-19 12:00');

        $source = $this->source(['run_window_from' => 2, 'run_window_to' => 6]);

        $this->assertSame('2026-08-20 02:00', $source->windowOpensAt()->format('Y-m-d H:i'));

        Carbon::setTestNow();
    }
}
