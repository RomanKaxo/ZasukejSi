<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Profile;
use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Models\User;
use App\Services\Scraping\ProfileExistenceChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Kontrola, že importovaný profil na zdroji pořád je.
 *
 * Dívka, která přestala inzerovat, ze zdroje zmizí — a u nás zůstala navěky,
 * veřejně a jako by byla aktuální. Z toho, co může scrapovaný katalog udělat
 * špatně, je tohle jediné, co stojí návštěvníka něco skutečného: inzerát,
 * který nikam nevede.
 *
 * Druhé pravidlo je stejně důležité: **nic se nemaže samo**. Kontrola označí
 * a upozorní, rozhoduje člověk.
 */
class ScrapeProfileExistenceTest extends TestCase
{
    use RefreshDatabase;

    private function source(array $settings = []): ScrapeSource
    {
        return ScrapeSource::create([
            'name' => 'Příklad',
            'slug' => 'priklad',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => array_merge([
                'crawl_delay' => 0,
                'respect_robots' => false,
                'conditional_requests' => false,
                'existence_confirmations' => 2,
                'existence_interval_hours' => 24,
            ], $settings),
        ]);
    }

    private function imported(ScrapeSource $source, string $url = 'https://example.test/p/1'): ScrapeItem
    {
        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'status' => 'approved',
        ]);

        return ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => $url,
            'external_id' => '1',
            'status' => ScrapeItem::STATUS_IMPORTED,
            'imported_profile_id' => $profile->id,
            'imported_at' => now(),
        ]);
    }

    /** Jedna 404 nic nedokazuje — weby stěhují stránky a mají výpadky. */
    public function test_one_miss_is_not_enough(): void
    {
        Http::fake(['https://example.test/*' => Http::response('Nope', 404)]);

        $source = $this->source();
        $item = $this->imported($source);

        $result = app(ProfileExistenceChecker::class)->check($source);

        $this->assertSame(0, $result['missing']);
        $this->assertSame(1, $item->fresh()->missing_checks);
        $this->assertNull($item->fresh()->missing_since);
    }

    public function test_a_repeated_miss_flags_the_profile_for_a_decision(): void
    {
        Http::fake(['https://example.test/*' => Http::response('Nope', 404)]);

        $source = $this->source();
        $item = $this->imported($source);
        $checker = app(ProfileExistenceChecker::class);

        $checker->check($source);
        $item->forceFill(['missing_checked_at' => now()->subDays(2)])->save();

        $result = $checker->check($source->fresh());

        $this->assertSame(1, $result['missing']);
        $this->assertTrue($item->fresh()->isAwaitingRemovalDecision());
    }

    /** A hlavně: profil zůstává, jak byl. Nic se nesmazalo ani neskrylo. */
    public function test_nothing_is_deleted_or_hidden_automatically(): void
    {
        Http::fake(['https://example.test/*' => Http::response('Nope', 404)]);

        $source = $this->source(['existence_confirmations' => 1]);
        $item = $this->imported($source);

        app(ProfileExistenceChecker::class)->check($source);

        $profile = $item->fresh()->profile;

        $this->assertNotNull($profile, 'Profil se nesmí smazat.');
        $this->assertSame('approved', $profile->status, 'Profil se nesmí ani skrýt.');
    }

    public function test_the_admin_is_told(): void
    {
        Http::fake(['https://example.test/*' => Http::response('Nope', 404)]);

        $source = $this->source(['existence_confirmations' => 1]);
        $this->imported($source);

        app(ProfileExistenceChecker::class)->check($source);

        $notification = Notification::where('is_global', true)->first();

        $this->assertNotNull($notification, 'Fronta, o které nikdo neví, je fronta, kterou nikdo nevyprázdní.');
        $this->assertStringContainsString('Příklad', $notification->message);
    }

    /** 403 znamená, že nás web odmítá — o existenci profilu neříká nic. */
    public function test_a_refusal_is_not_evidence_of_absence(): void
    {
        Http::fake(['https://example.test/*' => Http::response('Forbidden', 403)]);

        $source = $this->source(['existence_confirmations' => 1]);
        $item = $this->imported($source);

        $result = app(ProfileExistenceChecker::class)->check($source);

        $this->assertSame(0, $result['missing']);
        $this->assertSame(0, $item->fresh()->missing_checks);
        $this->assertNull($item->fresh()->missing_since);
    }

    /** Když se dívka na zdroj vrátí, značka zmizí. */
    public function test_a_profile_that_comes_back_is_cleared(): void
    {
        $source = $this->source(['existence_confirmations' => 1]);

        $item = $this->imported($source);
        $item->forceFill(['missing_since' => now()->subDay(), 'missing_checks' => 3])->save();

        Http::fake(['https://example.test/*' => Http::response('<h1>Profil</h1>', 200, ['Content-Type' => 'text/html'])]);

        $result = app(ProfileExistenceChecker::class)->check($source);

        $this->assertSame(1, $result['recovered']);
        $this->assertNull($item->fresh()->missing_since);
        $this->assertSame(0, $item->fresh()->missing_checks);
    }

    /** Co už někdo rozhodl, se znovu neptá. */
    public function test_a_decided_item_is_left_alone(): void
    {
        Http::fake(['https://example.test/*' => Http::response('Nope', 404)]);

        $source = $this->source(['existence_confirmations' => 1]);
        $item = $this->imported($source);

        $item->forceFill([
            'missing_since' => now()->subDays(3),
            'missing_checks' => 5,
            'missing_resolution' => ScrapeItem::MISSING_KEPT,
        ])->save();

        $result = app(ProfileExistenceChecker::class)->check($source);

        $this->assertSame(0, $result['checked']);
        $this->assertFalse($item->fresh()->isAwaitingRemovalDecision());
    }

    /** Položka bez profilu se nekontroluje — není co řešit. */
    public function test_items_without_a_profile_are_not_checked(): void
    {
        Http::fake(['https://example.test/*' => Http::response('Nope', 404)]);

        $source = $this->source();

        ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/p/2',
            'external_id' => '2',
            'status' => ScrapeItem::STATUS_PENDING,
        ]);

        $this->assertSame(0, app(ProfileExistenceChecker::class)->check($source)['checked']);
    }

    /** Pozastavený zdroj by hlásil, že zmizely úplně všechny profily. */
    public function test_the_command_skips_a_paused_source(): void
    {
        Http::fake(['https://example.test/*' => Http::response('Nope', 404)]);

        $source = $this->source(['existence_confirmations' => 1]);
        $this->imported($source);

        $source->forceFill(['paused_at' => now(), 'paused_reason' => 'HTTP 403'])->save();

        $this->artisan('scrape:verify')
            ->expectsOutputToContain('je pozastavený')
            ->assertSuccessful();

        $this->assertSame(0, ScrapeItem::query()->missingAtSource()->count());
    }
}
