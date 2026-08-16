<?php

namespace Tests\Feature;

use App\Filament\Widgets\OperationsOverview;
use App\Filament\Widgets\RecentScrapeRuns;
use App\Models\ContactMessage;
use App\Models\Profile;
use App\Models\ScrapeItem;
use App\Models\ScrapeRun;
use App\Models\ScrapeSource;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The dashboard is the screen an operator lands on, so what is waiting for
 * them has to be on it. The scraper queue and the contact form both arrive
 * from outside and notify nobody.
 */
class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $this->seed(CurrencySeeder::class);

        $user = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
        $user->syncRoles(['super_admin', 'admin']);

        return $user->fresh();
    }

    private function source(): ScrapeSource
    {
        return ScrapeSource::create([
            'name' => 'Zdroj',
            'slug' => 'zdroj',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => [],
        ]);
    }

    public function test_the_dashboard_counts_what_is_waiting(): void
    {
        $admin = $this->admin();

        Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'status' => 'pending',
        ]);

        ContactMessage::create([
            'first_name' => 'Jan',
            'last_name' => 'Novák',
            'email' => 'jan@example.com',
            'message' => 'Dotaz.',
        ]);

        ScrapeItem::create([
            'scrape_source_id' => $this->source()->id,
            'source_url' => 'https://example.test/1',
            'external_id' => '1',
            'content_hash' => 'a',
            'normalized' => ['display_name' => 'Jana'],
            'images' => [],
            'status' => ScrapeItem::STATUS_PENDING,
        ]);

        $this->actingAs($admin)->get('/admin')->assertSuccessful();

        // Widgets load deferred, so their contents are not in the first
        // response — they are asserted where they actually render.
        Livewire::actingAs($admin)
            ->test(OperationsOverview::class)
            ->assertSee(__('filament.dashboard.unread_messages'))
            ->assertSee(__('filament.dashboard.scrape_queue'))
            ->assertSee(__('filament.dashboard.pending_profiles'));
    }

    /**
     * Nothing about the scraper is shown until the scraper has been used —
     * an empty table on the landing screen is noise.
     */
    public function test_the_runs_panel_stays_hidden_until_there_is_a_run(): void
    {
        $this->admin();

        $this->assertFalse(RecentScrapeRuns::canView());

        ScrapeRun::create([
            'scrape_source_id' => $this->source()->id,
            'status' => ScrapeRun::STATUS_COMPLETED,
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'pages_fetched' => 1,
            'items_found' => 4,
            'items_new' => 4,
            'items_updated' => 0,
            'items_failed' => 0,
        ]);

        $this->assertTrue(RecentScrapeRuns::canView());
    }

    public function test_the_runs_panel_lists_the_latest_run(): void
    {
        $admin = $this->admin();

        ScrapeRun::create([
            'scrape_source_id' => $this->source()->id,
            'status' => ScrapeRun::STATUS_COMPLETED,
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'pages_fetched' => 1,
            'items_found' => 4,
            'items_new' => 4,
            'items_updated' => 0,
            'items_failed' => 0,
        ]);

        Livewire::actingAs($admin)
            ->test(RecentScrapeRuns::class)
            ->assertSee('Zdroj')
            ->assertSee('4');
    }

    public function test_the_dashboard_renders_with_nothing_in_the_database(): void
    {
        // Every figure has to survive an empty install, which is what a fresh
        // deploy looks like before anybody uses it.
        $this->actingAs($this->admin())->get('/admin')->assertSuccessful();
    }
}
