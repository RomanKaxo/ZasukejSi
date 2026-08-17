<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Importing was a per-row action in the admin, so a harvest of two dozen
 * profiles meant two dozen confirmations — and photo downloads wait out the
 * source's crawl delay, which is not something to sit through in a browser.
 */
class ScrapeImportCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    private function source(string $slug = 'test'): ScrapeSource
    {
        return ScrapeSource::create([
            'name' => 'Test ' . $slug,
            'slug' => $slug,
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => [],
        ]);
    }

    private function item(ScrapeSource $source, string $name, array $overrides = []): ScrapeItem
    {
        return ScrapeItem::create(array_merge([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/' . uniqid(),
            'external_id' => uniqid(),
            'content_hash' => uniqid(),
            'normalized' => ['display_name' => $name, 'city' => 'Brno', 'age' => 25],
            'images' => [],
            'status' => ScrapeItem::STATUS_PENDING,
        ], $overrides));
    }

    public function test_without_approve_only_approved_items_are_imported(): void
    {
        $source = $this->source();
        $this->item($source, 'Čekající');
        $this->item($source, 'Schválená', ['status' => ScrapeItem::STATUS_APPROVED]);

        $this->artisan('scrape:import', ['source' => 'test', '--without-images' => true])
            ->assertSuccessful();

        $this->assertSame(1, Profile::count());
        $this->assertSame('Schválená', Profile::first()->display_name);
    }

    public function test_approve_takes_the_pending_ones_too(): void
    {
        $source = $this->source();
        $this->item($source, 'Jedna');
        $this->item($source, 'Dvě');

        $this->artisan('scrape:import', ['source' => 'test', '--approve' => true, '--without-images' => true])
            ->assertSuccessful();

        $this->assertSame(2, Profile::count());
    }

    /**
     * The whole point of the review queue: nothing this creates is on the site.
     */
    public function test_every_created_profile_is_unpublished_and_pending(): void
    {
        $this->item($this->source(), 'Jana');

        $this->artisan('scrape:import', ['--approve' => true, '--without-images' => true]);

        $profile = Profile::sole();

        $this->assertSame('pending', $profile->status);
        $this->assertFalse((bool) $profile->is_public);
        $this->assertNull($profile->user_id);
    }

    public function test_the_limit_is_respected(): void
    {
        $source = $this->source();

        foreach (['A', 'B', 'C'] as $name) {
            $this->item($source, $name);
        }

        $this->artisan('scrape:import', ['--approve' => true, '--limit' => 2, '--without-images' => true]);

        $this->assertSame(2, Profile::count());
    }

    public function test_duplicates_can_be_left_alone(): void
    {
        $source = $this->source();

        Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'display_name' => ['cs' => 'Anička', 'en' => 'Anička'],
            'city' => 'Brno',
        ]);

        $this->item($source, 'Anička');
        $this->item($source, 'Někdo jiný');

        $this->artisan('scrape:import', [
            '--approve' => true,
            '--skip-duplicates' => true,
            '--without-images' => true,
        ]);

        // One existing profile plus the one that is not a duplicate.
        $this->assertSame(2, Profile::count());
        $this->assertTrue(Profile::query()->where('display_name->cs', 'Někdo jiný')->exists());
    }

    /**
     * One bad row must not stop the rest, and the reason has to stay on it.
     */
    public function test_an_item_without_a_name_fails_alone(): void
    {
        $source = $this->source();

        $broken = $this->item($source, 'x', ['normalized' => ['city' => 'Brno']]);
        $this->item($source, 'V pořádku');

        $this->artisan('scrape:import', ['--approve' => true, '--without-images' => true])
            ->assertFailed();

        $this->assertSame(1, Profile::count());
        $this->assertSame(ScrapeItem::STATUS_FAILED, $broken->fresh()->status);
        $this->assertNotNull($broken->fresh()->error);
    }

    public function test_an_unknown_source_is_reported(): void
    {
        $this->artisan('scrape:import', ['source' => 'neexistuje'])->assertFailed();
    }

    public function test_nothing_to_import_is_not_a_failure(): void
    {
        $this->source();

        $this->artisan('scrape:import', ['source' => 'test'])->assertSuccessful();
    }
}
