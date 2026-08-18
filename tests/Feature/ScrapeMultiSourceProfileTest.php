<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Models\User;
use App\Services\Scraping\ProfileExistenceChecker;
use App\Services\Scraping\ScrapeItemImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

/**
 * Tatáž dívka na několika webech.
 *
 * Import každého katalogu udělal další profil a jediným lékem bylo dva ručně
 * smazat — načež je další běh vyrobil znovu, protože nic nezaznamenalo, že je
 * otázka vyřešená.
 */
class ScrapeMultiSourceProfileTest extends TestCase
{
    use RefreshDatabase;

    private function source(string $slug): ScrapeSource
    {
        return ScrapeSource::create([
            'name' => 'Katalog ' . $slug,
            'slug' => $slug,
            'base_url' => 'https://' . $slug . '.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => [
                'crawl_delay' => 0,
                'respect_robots' => false,
                'existence_confirmations' => 1,
            ],
        ]);
    }

    private function profile(): Profile
    {
        return Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'status' => 'approved',
        ]);
    }

    private function item(ScrapeSource $source, ?Profile $profile = null, array $attributes = []): ScrapeItem
    {
        return ScrapeItem::create(array_merge([
            'scrape_source_id' => $source->id,
            'source_url' => $source->base_url . '/p/1',
            'external_id' => '1',
            'status' => $profile ? ScrapeItem::STATUS_IMPORTED : ScrapeItem::STATUS_APPROVED,
            'imported_profile_id' => $profile?->id,
            'imported_at' => $profile ? now() : null,
            'normalized' => ['display_name' => 'Kristýna', 'city' => 'Brno'],
        ], $attributes));
    }

    public function test_attaching_does_not_create_a_second_profile(): void
    {
        $profile = $this->profile();
        $item = $this->item($this->source('druhy'));

        app(ScrapeItemImporter::class)->attachTo($item, $profile, false);

        $this->assertSame(1, Profile::count());
        $this->assertSame($profile->id, $item->fresh()->imported_profile_id);
        $this->assertSame(ScrapeItem::STATUS_IMPORTED, $item->fresh()->status);
    }

    /** Připojení je odpověď na otázku „není to duplicita?". */
    public function test_attaching_clears_the_duplicate_flag(): void
    {
        $profile = $this->profile();
        $item = $this->item($this->source('druhy'), null, [
            'duplicate_profile_id' => $profile->id,
            'duplicate_reason' => 'shodné jméno a město',
        ]);

        app(ScrapeItemImporter::class)->attachTo($item, $profile, false);

        $this->assertFalse($item->fresh()->hasDuplicate());
    }

    /** Co je v profilu vyplněné, je něčí rozhodnutí — druhý katalog ho nepřebije. */
    public function test_attaching_never_overwrites_what_the_profile_already_has(): void
    {
        $profile = $this->profile();
        $profile->forceFill(['display_name' => 'Kristýna P.', 'city' => 'Praha'])->save();

        $item = $this->item($this->source('druhy'));

        app(ScrapeItemImporter::class)->attachTo($item, $profile, false);

        $profile->refresh();

        $this->assertSame('Kristýna P.', $profile->display_name);
        $this->assertSame('Praha', $profile->city);
    }

    public function test_an_item_cannot_be_attached_to_two_profiles(): void
    {
        $first = $this->profile();
        $second = $this->profile();

        $item = $this->item($this->source('druhy'), $first);

        $this->expectException(RuntimeException::class);

        app(ScrapeItemImporter::class)->attachTo($item, $second, false);
    }

    /**
     * Tohle je ten důvod, proč se to celé propojuje: že ji jeden web stáhl,
     * neznamená, že skončila.
     */
    public function test_a_profile_still_listed_elsewhere_is_not_offered_for_removal(): void
    {
        $profile = $this->profile();

        $prvni = $this->source('prvni');
        $druhy = $this->source('druhy');

        $gone = $this->item($prvni, $profile);
        $this->item($druhy, $profile, ['source_url' => 'https://druhy.test/p/9', 'external_id' => '9']);

        Http::fake(['https://prvni.test/*' => Http::response('Nope', 404)]);

        app(ProfileExistenceChecker::class)->check($prvni);

        $gone->refresh();

        $this->assertNotNull($gone->missing_since, 'Ten web ji opravdu ztratil.');
        $this->assertFalse($gone->isAwaitingRemovalDecision(), 'Ale jinde pořád inzeruje.');
        $this->assertSame(0, ScrapeItem::query()->missingAtSource()->count());
    }

    /** Jakmile ji ztratí i poslední zdroj, rozhodnutí se vyžádá. */
    public function test_when_the_last_source_loses_her_the_decision_is_asked_for(): void
    {
        $profile = $this->profile();

        $prvni = $this->source('prvni');
        $druhy = $this->source('druhy');

        $this->item($prvni, $profile);
        $this->item($druhy, $profile, ['source_url' => 'https://druhy.test/p/9', 'external_id' => '9']);

        Http::fake([
            'https://prvni.test/*' => Http::response('Nope', 404),
            'https://druhy.test/*' => Http::response('Nope', 404),
        ]);

        $checker = app(ProfileExistenceChecker::class);
        $checker->check($prvni);
        $checker->check($druhy);

        $this->assertSame(2, ScrapeItem::query()->missingAtSource()->count());
    }

    /** Profil ví, ze kterých katalogů je poskládaný. */
    public function test_a_profile_lists_its_sources(): void
    {
        $profile = $this->profile();

        $this->item($this->source('prvni'), $profile);
        $this->item($this->source('druhy'), $profile, [
            'source_url' => 'https://druhy.test/p/9',
            'external_id' => '9',
        ]);

        $this->assertSame(2, $profile->scrapeItems()->count());
    }
}
