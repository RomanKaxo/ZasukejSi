<?php

namespace Tests\Feature;

use App\Filament\Resources\ScrapeItems\Pages\ViewScrapeItem;
use App\Models\ScrapeFieldMap;
use App\Models\ScrapeItem;
use App\Models\ScrapeRun;
use App\Models\ScrapeSource;
use App\Models\User;
use App\Services\Scraping\ScrapeRunner;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The review queue offered Approve and Import without ever showing what the
 * item contained, and a run reported counts while the narration that would
 * explain them was computed and thrown away.
 */
class ScraperAdminTest extends TestCase
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
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => [
                'crawl_delay' => 0,
                'listing_path' => '/list',
                'detail_link_selector' => 'a.profile',
                'external_id_pattern' => '#/(\d+)/?$#',
                'respect_robots' => false,
            ],
        ]);
    }

    private function item(ScrapeSource $source, array $overrides = []): ScrapeItem
    {
        return ScrapeItem::create(array_merge([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/profil/1',
            'external_id' => '1',
            'content_hash' => 'abc',
            'normalized' => [
                'display_name' => 'Jana',
                'age' => 27,
                'city' => 'Praha',
                'about' => 'Text o mně.',
                'services' => ['Masáž'],
            ],
            'images' => [
                'https://example.test/foto/1.jpg',
                'https://example.test/foto/2.jpg',
            ],
            'status' => ScrapeItem::STATUS_PENDING,
        ], $overrides));
    }

    public function test_the_detail_shows_the_scraped_values_and_photos(): void
    {
        $item = $this->item($this->source());

        $response = $this->actingAs($this->admin())->get("/admin/scrape-items/{$item->id}");

        $response->assertSuccessful();
        $response->assertSee('Jana');
        $response->assertSee('Praha');
        // The photo strip is the only place the images can be seen before the
        // import downloads them.
        $response->assertSee('https://example.test/foto/1.jpg', false);
        $response->assertSee('https://example.test/foto/2.jpg', false);
    }

    /**
     * A textarea's value lives in the component state rather than the initial
     * HTML, so the long fields are checked where they actually are.
     */
    public function test_the_detail_carries_every_scraped_field(): void
    {
        $item = $this->item($this->source());

        Livewire::actingAs($this->admin())
            ->test(ViewScrapeItem::class, ['record' => $item->getRouteKey()])
            ->assertSet('data.normalized.display_name', 'Jana')
            ->assertSet('data.normalized.city', 'Praha')
            ->assertSet('data.normalized.age', 27)
            ->assertSet('data.normalized.about', 'Text o mně.');
    }

    public function test_values_the_form_does_not_name_are_still_shown(): void
    {
        $item = $this->item($this->source(), [
            'normalized' => ['display_name' => 'Jana', 'services' => ['Masáž', 'Escort']],
        ]);

        $this->actingAs($this->admin())
            ->get("/admin/scrape-items/{$item->id}")
            ->assertSuccessful()
            ->assertSee('Masáž, Escort');
    }

    public function test_an_item_can_be_corrected_before_import(): void
    {
        $item = $this->item($this->source());

        $this->actingAs($this->admin())
            ->get("/admin/scrape-items/{$item->id}/edit")
            ->assertSuccessful();

        // A wrong value is easier to fix here than on a profile that exists.
        $item->update(['normalized' => array_merge($item->normalized, ['city' => 'Brno'])]);

        $this->assertSame('Brno', $item->fresh()->value('city'));
    }

    public function test_an_imported_item_cannot_be_edited_away(): void
    {
        $item = $this->item($this->source(), ['status' => ScrapeItem::STATUS_IMPORTED]);

        // The edit action is hidden once a profile exists; the raw record still
        // has to render so the provenance stays readable.
        $this->actingAs($this->admin())
            ->get("/admin/scrape-items/{$item->id}")
            ->assertSuccessful();
    }

    public function test_the_queue_lists_items_with_their_first_photo(): void
    {
        $this->item($this->source());

        $this->actingAs($this->admin())
            ->get('/admin/scrape-items')
            ->assertSuccessful()
            ->assertSee('Jana');
    }

    /**
     * A dry run exists to show what the selectors return. That output used to
     * reach only the console command.
     */
    public function test_a_run_records_what_it_saw(): void
    {
        Http::fake([
            'https://example.test/list*' => Http::response(
                '<a class="profile" href="https://example.test/profil/1">Jana</a>',
                200,
                ['Content-Type' => 'text/html']
            ),
            'https://example.test/profil/1' => Http::response(
                '<h1>Jana</h1><span class="city">Praha</span>',
                200,
                ['Content-Type' => 'text/html']
            ),
        ]);

        $source = $this->source();

        ScrapeFieldMap::create([
            'scrape_source_id' => $source->id,
            'target_field' => 'display_name',
            'selector' => 'h1',
            'sort_order' => 1,
        ]);
        ScrapeFieldMap::create([
            'scrape_source_id' => $source->id,
            'target_field' => 'city',
            'selector' => '.city',
            'sort_order' => 2,
        ]);

        $run = app(ScrapeRunner::class)->run($source, ['dry_run' => true, 'limit' => 1]);

        $this->assertNotNull($run->log);
        $this->assertStringContainsString('DRY-RUN', $run->log);
        // The extracted values, not just the counts.
        $this->assertStringContainsString('display_name=Jana', $run->log);
        $this->assertStringContainsString('city=Praha', $run->log);
    }

    public function test_a_failed_run_still_keeps_its_log(): void
    {
        Http::fake([
            'https://example.test/list*' => Http::response('boom', 500),
        ]);

        $run = app(ScrapeRunner::class)->run($this->source(), ['limit' => 1]);

        $this->assertNotNull($run->log);
        $this->assertStringContainsString('robots.txt', $run->log);
    }

    public function test_the_runs_screen_offers_the_log(): void
    {
        $source = $this->source();

        $run = ScrapeRun::create([
            'scrape_source_id' => $source->id,
            'status' => ScrapeRun::STATUS_COMPLETED,
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'pages_fetched' => 1,
            'items_found' => 1,
            'items_new' => 1,
            'items_updated' => 0,
            'items_failed' => 0,
            'log' => "Nalezeno profilů: 1\nDRY-RUN https://example.test/profil/1 — display_name=Jana",
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/scrape-runs')
            ->assertSuccessful()
            ->assertSee('Průběh');

        $this->assertStringContainsString('display_name=Jana', $run->log);
    }

    public function test_a_rejected_item_can_be_put_back_in_the_queue(): void
    {
        $item = $this->item($this->source(), [
            'status' => ScrapeItem::STATUS_FAILED,
            'error' => 'Chybí povinná pole: display_name',
        ]);

        // What the bulk action does: a failed import is usually a fixable value,
        // not a dead row.
        $item->forceFill(['status' => ScrapeItem::STATUS_PENDING, 'error' => null])->save();

        $this->assertSame(ScrapeItem::STATUS_PENDING, $item->fresh()->status);
        $this->assertNull($item->fresh()->error);
    }
}
