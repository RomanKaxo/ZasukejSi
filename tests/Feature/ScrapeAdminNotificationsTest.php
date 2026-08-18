<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Profile;
use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Models\User;
use App\Services\Scraping\ProfileExistenceChecker;
use App\Services\Scraping\ScrapeRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Provozní zprávy patří administraci, ne návštěvníkům.
 *
 * Kanál byl jeden a byl to zvonek na webu. „Profily zmizely ze zdroje" tedy
 * chodilo jako globální notifikace — každé dívce a každému členovi, o údržbě,
 * se kterou nemůžou nic dělat.
 */
class ScrapeAdminNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function source(array $settings = []): ScrapeSource
    {
        $source = ScrapeSource::create([
            'name' => 'Příklad',
            'slug' => 'priklad',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'schedule_hours' => 24,
            'settings' => array_merge([
                'crawl_delay' => 0,
                'listing_path' => '/list',
                'detail_link_selector' => 'a.profile',
                'respect_robots' => false,
                'conditional_requests' => false,
                'existence_confirmations' => 1,
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

    private function fakeHarvest(): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">A</a>',
                200,
                ['Content-Type' => 'text/html'],
            ),
            'https://example.test/p/1' => Http::response('<h1>Kristýna</h1>', 200, ['Content-Type' => 'text/html']),
        ]);
    }

    /** To hlavní: do zvonku na webu se provozní zpráva nesmí dostat. */
    public function test_an_operational_notice_never_reaches_a_visitor(): void
    {
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);

        Notification::forAdmins('Scraper selhal', 'Web nás odmítl.');

        $this->assertSame(0, Notification::query()->forUser($user->id)->count());
        $this->assertSame(1, Notification::query()->forAdmins()->count());
    }

    /** Zpráva pro návštěvníky se tím nesmí ztratit. */
    public function test_a_public_notice_still_reaches_the_visitor(): void
    {
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);

        Notification::create([
            'user_id' => null,
            'is_global' => true,
            'title' => 'Novinka',
            'message' => 'Něco pro návštěvníky.',
        ]);

        $this->assertSame(1, Notification::query()->forUser($user->id)->count());
    }

    public function test_a_scheduled_run_that_found_something_reports_it(): void
    {
        $this->fakeHarvest();

        app(ScrapeRunner::class)->run($this->source(), ['pages' => 1, 'scheduled' => true]);

        $notice = Notification::query()->forAdmins()->first();

        $this->assertNotNull($notice);
        $this->assertStringContainsString('Příklad', $notice->title);
        $this->assertStringContainsString('nových 1', $notice->message);
    }

    /**
     * Běh, který nenašel nic nového, je systém fungující podle plánu.
     * Notifikace o tom každou noc je způsob, jak přestat notifikace číst.
     */
    public function test_a_quiet_run_says_nothing(): void
    {
        $this->fakeHarvest();

        $source = $this->source();
        $runner = app(ScrapeRunner::class);

        $runner->run($source, ['pages' => 1, 'scheduled' => true]);
        Notification::query()->delete();

        $runner->run($source->fresh(), ['pages' => 1, 'scheduled' => true]);

        $this->assertSame(0, Notification::query()->forAdmins()->count());
    }

    /** Ruční běh nehlásí nic: obsluha u toho seděla a výsledek viděla. */
    public function test_a_manual_run_is_silent(): void
    {
        $this->fakeHarvest();

        $source = $this->source();
        $source->forceFill(['schedule_hours' => null])->save();

        app(ScrapeRunner::class)->run($source, ['pages' => 1]);

        $this->assertSame(0, Notification::query()->forAdmins()->count());
    }

    public function test_a_failed_scheduled_run_is_reported(): void
    {
        Http::fake(['https://example.test/*' => Http::response('Forbidden', 403)]);

        app(ScrapeRunner::class)->run($this->source(), ['pages' => 1, 'scheduled' => true]);

        $notice = Notification::query()->forAdmins()->where('type', 'error')->first();

        $this->assertNotNull($notice);
        $this->assertStringContainsString('selhal', $notice->title);
    }

    public function test_pausing_a_source_is_reported(): void
    {
        Http::fake(['https://example.test/*' => Http::response('Forbidden', 403)]);

        $source = $this->source(['failure_threshold' => 1]);

        app(ScrapeRunner::class)->run($source, ['pages' => 1, 'scheduled' => true]);

        $this->assertSame(
            1,
            Notification::query()->forAdmins()->where('title', 'like', '%pozastavil%')->count(),
        );
    }

    public function test_vanished_profiles_are_reported_to_the_admin_only(): void
    {
        $source = $this->source();

        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
        ]);

        ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/p/1',
            'external_id' => '1',
            'status' => ScrapeItem::STATUS_IMPORTED,
            'imported_profile_id' => $profile->id,
            'imported_at' => now(),
        ]);

        Http::fake(['https://example.test/*' => Http::response('Nope', 404)]);

        app(ProfileExistenceChecker::class)->check($source);

        $notice = Notification::query()->forAdmins()->first();

        $this->assertNotNull($notice);
        $this->assertSame(Notification::AUDIENCE_ADMIN, $notice->audience);
        $this->assertFalse((bool) $notice->is_global);
    }
}
