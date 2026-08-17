<?php

namespace Tests\Feature;

use App\Filament\Resources\ScrapeSources\Pages\ListScrapeSources;
use App\Models\ScrapeSource;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The scraper's buttons in the admin.
 *
 * The test run reported its result through a notification that carried a link,
 * and the link's class — `Filament\Notifications\Actions\Action` — no longer
 * exists in Filament 4. The run itself worked; it fell over at the moment it
 * tried to say so, which the admin saw as „Class ... not found".
 *
 * Rendering the page never caught it, because the action only builds when it
 * is called. So it is called here.
 */
class ScraperAdminActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
        $this->seed(RoleSeeder::class);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['email' => 'test@example.com']);
        $admin->syncRoles(['admin', 'super_admin']);

        return $admin;
    }

    private function source(): ScrapeSource
    {
        return ScrapeSource::create([
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => ['crawl_delay' => 0],
        ]);
    }

    public function test_the_test_run_reports_its_result_without_blowing_up(): void
    {
        Http::fake([
            '*/robots.txt' => Http::response('User-agent: *' . PHP_EOL . 'Allow: /', 200),
            '*' => Http::response('<html><body><h1>Jana</h1></body></html>', 200),
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListScrapeSources::class)
            ->callTableAction('testRun', $this->source(), [
                'url' => 'https://example.test/p/1/',
                'limit' => 1,
                'dry_run' => true,
            ])
            ->assertHasNoTableActionErrors()
            // The success branch specifically: that is the one that builds the
            // notification's link, and the one that used to explode.
            ->assertNotified('Běh dokončen');
    }

    public function test_a_failing_run_is_reported_rather_than_thrown(): void
    {
        Http::fake([
            '*/robots.txt' => Http::response('User-agent: *' . PHP_EOL . 'Disallow: /', 200),
            '*' => Http::response('', 500),
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListScrapeSources::class)
            ->callTableAction('testRun', $this->source(), [
                'url' => 'https://example.test/p/1/',
                'limit' => 1,
                'dry_run' => true,
            ])
            ->assertHasNoTableActionErrors()
            ->assertNotified();
    }
}
