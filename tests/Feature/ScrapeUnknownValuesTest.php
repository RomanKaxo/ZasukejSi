<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Profile;
use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Models\ScrapeUnknownValue;
use App\Models\Service;
use App\Services\Scraping\ScrapeItemImporter;
use App\Services\Scraping\UnknownValueCollector;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The importer never invents catalogue entries — a scraped list must not be
 * able to extend our own taxonomy. That rule was right but silent: a harvest
 * of Brno brought 58 distinct service names, the catalogue knew 10, and the
 * other 48 were dropped without anybody being told.
 */
class ScrapeUnknownValuesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);

        // The queue covers every field we can process, not just services, so a
        // town nobody has heard of is a gap of its own. These tests are about
        // service gaps, so the town they use is known.
        City::create(['name' => 'Brno', 'name_ascii' => 'Brno', 'country_code' => 'CZ']);
    }

    private ?ScrapeSource $source = null;

    private function source(): ScrapeSource
    {
        // One source per test; `slug` is unique, so making a fresh one for
        // every item blows up on the second call.
        return $this->source ??= ScrapeSource::create([
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => [],
        ]);
    }

    private function service(string $name): Service
    {
        return Service::create([
            'name' => ['cs' => $name, 'en' => $name],
            'description' => ['cs' => '', 'en' => ''],
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    private function item(array $services, array $overrides = []): ScrapeItem
    {
        return ScrapeItem::create(array_merge([
            'scrape_source_id' => $this->source()->id,
            'source_url' => 'https://example.test/' . uniqid(),
            'external_id' => uniqid(),
            'content_hash' => uniqid(),
            'normalized' => ['display_name' => 'Jana', 'city' => 'Brno', 'services' => $services],
            'images' => [],
            'status' => ScrapeItem::STATUS_PENDING,
        ], $overrides));
    }

    // --- rozpoznání ------------------------------------------------------

    public function test_a_name_the_catalogue_knows_is_not_reported(): void
    {
        $this->service('Erotická masáž');

        $unknown = app(UnknownValueCollector::class)->unknownServices($this->item(['Erotická masáž']));

        $this->assertTrue($unknown->isEmpty());
    }

    public function test_spelling_does_not_make_a_second_gap(): void
    {
        $this->service('GFE - společnice');

        // Two sites will not spell a service the same way.
        $unknown = app(UnknownValueCollector::class)->unknownServices($this->item(['gfe spolecnice', 'GFE – Společnice']));

        $this->assertTrue($unknown->isEmpty());
    }

    public function test_an_unknown_name_is_reported(): void
    {
        $this->service('Erotická masáž');

        $unknown = app(UnknownValueCollector::class)->unknownServices($this->item(['Erotická masáž', 'Striptýz']));

        $this->assertSame(['Striptýz'], $unknown->all());
    }

    // --- fronta ----------------------------------------------------------

    public function test_the_queue_counts_occurrences_instead_of_duplicating(): void
    {
        $collector = app(UnknownValueCollector::class);

        $collector->collect($this->item(['Striptýz']));
        $collector->collect($this->item(['striptyz']));
        $collector->collect($this->item(['Striptýz']));

        $row = ScrapeUnknownValue::sole();

        $this->assertSame('Striptýz', $row->value);
        $this->assertSame(3, $row->occurrences);
    }

    public function test_approving_creates_the_catalogue_entry(): void
    {
        app(UnknownValueCollector::class)->collect($this->item(['Striptýz']));

        $service = ScrapeUnknownValue::sole()->approve();

        $this->assertInstanceOf(Service::class, $service);
        $this->assertSame('Striptýz', $service->getTranslation('name', 'cs'));
        $this->assertSame(ScrapeUnknownValue::STATUS_APPROVED, ScrapeUnknownValue::sole()->status);
    }

    public function test_an_entry_that_already_matches_is_not_created_twice(): void
    {
        // Noted while the catalogue was empty…
        app(UnknownValueCollector::class)->collect($this->item(['Striptyz']));

        // …and added by hand in the meantime, in a different spelling.
        $existing = $this->service('Striptýz');

        $service = ScrapeUnknownValue::sole()->approve();

        // Adopting beats duplicating; the gap was only a spelling.
        $this->assertSame($existing->id, $service->id);
        $this->assertSame(1, Service::count());
    }

    public function test_the_admin_can_reword_it_before_it_becomes_permanent(): void
    {
        app(UnknownValueCollector::class)->collect($this->item(['striptyz  ']));

        $service = ScrapeUnknownValue::sole()->approve('Striptýz');

        $this->assertSame('Striptýz', $service->getTranslation('name', 'cs'));
    }

    public function test_rejecting_creates_nothing(): void
    {
        app(UnknownValueCollector::class)->collect($this->item(['Striptýz']));

        ScrapeUnknownValue::sole()->reject();

        $this->assertSame(0, Service::count());
        $this->assertSame(ScrapeUnknownValue::STATUS_REJECTED, ScrapeUnknownValue::sole()->status);
    }

    // --- co na hodnotu čekalo -------------------------------------------

    public function test_an_item_with_a_gap_is_incomplete_and_gets_flagged(): void
    {
        $collector = app(UnknownValueCollector::class);
        $item = $this->item(['Striptýz']);

        $collector->collect($item);

        $this->assertFalse($collector->isComplete($item));
        $this->assertTrue($item->fresh()->hasUnknownValues());
    }

    public function test_filling_the_gap_makes_the_item_complete(): void
    {
        $collector = app(UnknownValueCollector::class);
        $item = $this->item(['Striptýz']);
        $collector->collect($item);

        ScrapeUnknownValue::sole()->approve();
        $collector->forget();

        $this->assertTrue($collector->isComplete($item->fresh()));
        $this->assertTrue($collector->unblockedItems()->contains('id', $item->id));
    }

    /**
     * A profile imported while the catalogue knew ten services kept ten, even
     * after the missing ones were added — the names were dropped at import
     * time and nothing went back for them.
     */
    public function test_an_already_imported_profile_gets_the_rest_of_its_services(): void
    {
        $this->service('Erotická masáž');

        $collector = app(UnknownValueCollector::class);
        $importer = app(ScrapeItemImporter::class);

        $item = $this->item(['Erotická masáž', 'Striptýz']);
        $collector->collect($item);

        $item->update(['status' => ScrapeItem::STATUS_APPROVED]);
        $profile = $importer->import($item->fresh(), false);

        $this->assertSame(1, $profile->services()->count());

        ScrapeUnknownValue::query()->pending()->first()->approve();
        $collector->forget();

        $this->assertSame(2, $importer->resyncServices($item->fresh()));
    }

    public function test_a_profile_keeps_services_that_are_still_unknown_out(): void
    {
        $this->service('Erotická masáž');

        $item = $this->item(['Erotická masáž', 'Něco, co nevedeme']);
        $item->update(['status' => ScrapeItem::STATUS_APPROVED]);

        $profile = app(ScrapeItemImporter::class)->import($item, false);

        // The rule that started all this: a scraped list never extends our
        // catalogue by itself.
        $this->assertSame(1, $profile->services()->count());
        $this->assertSame(1, Service::count());
    }

    public function test_an_item_with_no_services_is_complete(): void
    {
        $item = $this->item([]);

        $this->assertTrue(app(UnknownValueCollector::class)->isComplete($item));
        $this->assertFalse($item->fresh()->hasUnknownValues());
    }

    // --- konzole ---------------------------------------------------------

    public function test_the_command_lists_without_changing_anything(): void
    {
        $this->item(['Striptýz']);

        $this->artisan('scrape:unknown-values', ['--list' => true])->assertSuccessful();

        $this->assertSame(0, ScrapeUnknownValue::count());
    }

    public function test_the_command_fills_the_queue_and_can_approve_everything(): void
    {
        $this->item(['Striptýz', 'Svazování']);

        $this->artisan('scrape:unknown-values', ['--approve-all' => true])->assertSuccessful();

        $this->assertSame(2, Service::count());
        $this->assertSame(0, ScrapeUnknownValue::query()->pending()->count());
    }

    public function test_the_command_releases_items_that_were_waiting(): void
    {
        $item = $this->item(['Striptýz']);
        app(UnknownValueCollector::class)->collect($item);

        $this->artisan('scrape:unknown-values', ['--approve-all' => true]);

        // Held back for a value, so once the value exists it does not need a
        // second visit from the reviewer.
        $this->assertSame(ScrapeItem::STATUS_APPROVED, $item->fresh()->status);
    }

    public function test_an_item_nobody_blocked_is_not_approved_behind_your_back(): void
    {
        $this->service('Erotická masáž');

        $item = $this->item(['Erotická masáž']);

        $this->artisan('scrape:unknown-values', ['--approve-all' => true]);

        $this->assertSame(ScrapeItem::STATUS_PENDING, $item->fresh()->status);
    }
}
