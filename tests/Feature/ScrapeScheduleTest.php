<?php

namespace Tests\Feature;

use App\Models\ScrapeItem;
use App\Models\ScrapeRun;
use App\Models\ScrapeSource;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * A source could only be harvested by somebody clicking a button or typing a
 * command, so anything recurring depended on remembering to do it.
 */
class ScrapeScheduleTest extends TestCase
{
    use RefreshDatabase;

    private function source(array $attributes = []): ScrapeSource
    {
        return ScrapeSource::create(array_merge([
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => [
                'crawl_delay' => 0,
                'listing_path' => '/list',
                'detail_link_selector' => 'a.profile',
                'respect_robots' => false,
            ],
        ], $attributes));
    }

    public function test_a_source_without_an_interval_never_runs_on_its_own(): void
    {
        $source = $this->source();

        // Every source that exists today is in this state; the deploy must not
        // start harvesting anything by itself.
        $this->assertFalse($source->isScheduled());
        $this->assertFalse($source->isDue());
        $this->assertSame(0, ScrapeSource::query()->due()->count());
    }

    public function test_a_disabled_source_is_never_due(): void
    {
        $source = $this->source(['is_enabled' => false, 'schedule_hours' => 1]);

        $this->assertFalse($source->isDue());
        $this->assertSame(0, ScrapeSource::query()->due()->count());
    }

    public function test_a_scheduled_source_with_no_slot_yet_is_due_at_once(): void
    {
        $source = $this->source(['schedule_hours' => 24]);

        $this->assertTrue($source->isDue());
        $this->assertSame(1, ScrapeSource::query()->due()->count());
    }

    public function test_a_future_slot_is_not_due(): void
    {
        $this->source(['schedule_hours' => 24, 'next_run_at' => now()->addHour()]);

        $this->assertSame(0, ScrapeSource::query()->due()->count());
    }

    public function test_the_slot_moves_forward_by_the_interval(): void
    {
        $source = $this->source(['schedule_hours' => 6]);

        $source->scheduleNextRun();

        // Counted from now, not from the previous slot: a run that took an hour
        // must not come due again immediately.
        $this->assertNotNull($source->next_run_at);
        $this->assertEqualsWithDelta(6 * 60, now()->diffInMinutes($source->next_run_at), 1);
    }

    public function test_clearing_the_interval_clears_the_slot(): void
    {
        $source = $this->source(['schedule_hours' => 6, 'next_run_at' => now()->addHour()]);

        $source->schedule_hours = null;
        $source->scheduleNextRun();

        $this->assertNull($source->fresh()->next_run_at);
    }

    public function test_the_command_runs_a_due_source_and_moves_its_slot(): void
    {
        Http::fake([
            'https://example.test/list*' => Http::response(
                '<a class="profile" href="https://example.test/profil/1">Jana</a>',
                200,
                ['Content-Type' => 'text/html']
            ),
            'https://example.test/profil/1' => Http::response('<h1>Jana</h1>', 200, ['Content-Type' => 'text/html']),
        ]);

        $source = $this->source(['schedule_hours' => 12]);

        $this->artisan('scrape:due')->assertSuccessful();

        $this->assertNotNull($source->fresh()->next_run_at);
        $this->assertSame(1, ScrapeRun::query()->count());
        $this->assertSame(1, ScrapeItem::query()->count());
    }

    public function test_the_command_leaves_a_source_that_is_not_due_alone(): void
    {
        $source = $this->source(['schedule_hours' => 12, 'next_run_at' => now()->addHours(6)]);

        $this->artisan('scrape:due')->assertSuccessful();

        $this->assertSame(0, ScrapeRun::query()->count());
        $this->assertTrue($source->fresh()->next_run_at->isFuture());
    }

    /**
     * A source that throws must not stay due forever, or every later run would
     * start with the same broken one.
     */
    public function test_a_failing_source_still_gets_its_slot_moved(): void
    {
        Http::fake([
            'https://example.test/list*' => Http::response('nope', 500),
        ]);

        $source = $this->source(['schedule_hours' => 3]);

        $this->artisan('scrape:due');

        $this->assertNotNull($source->fresh()->next_run_at);
    }

    public function test_force_runs_a_source_that_is_not_due(): void
    {
        Http::fake([
            'https://example.test/list*' => Http::response('', 200, ['Content-Type' => 'text/html']),
        ]);

        $this->source(['schedule_hours' => 12, 'next_run_at' => now()->addHours(6)]);

        $this->artisan('scrape:due', ['--force' => true])->assertSuccessful();

        $this->assertSame(1, ScrapeRun::query()->count());
    }

    public function test_the_source_listing_shows_the_schedule(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(CurrencySeeder::class);

        $admin = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
        $admin->syncRoles(['super_admin', 'admin']);

        $this->source(['schedule_hours' => 24]);

        $this->actingAs($admin)
            ->get('/admin/scrape-sources')
            ->assertSuccessful()
            ->assertSee('každých 24 h');
    }
}
