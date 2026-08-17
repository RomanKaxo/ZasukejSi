<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\ProfileAttributeOption;
use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Models\ScrapeUnknownValue;
use App\Services\Scraping\ScrapeItemImporter;
use App\Services\Scraping\UnknownValueCollector;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\ProfileAttributeOptionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The queue used to cover services only.
 *
 * Everything else a source offered went one of two ways: stored as free text
 * without anybody checking it, or — for eye colour, hair colour and length,
 * bust type, pubic hair and travel range — dropped, because the importer had
 * nowhere to put it and no list to check it against.
 */
class ScrapeAttributeQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
        $this->seed(ProfileAttributeOptionSeeder::class);

        City::create(['name' => 'Brno', 'name_ascii' => 'Brno', 'country_code' => 'CZ']);
    }

    private ?ScrapeSource $source = null;

    private function source(): ScrapeSource
    {
        return $this->source ??= ScrapeSource::create([
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => [],
        ]);
    }

    private function item(array $values = []): ScrapeItem
    {
        return ScrapeItem::create([
            'scrape_source_id' => $this->source()->id,
            'source_url' => 'https://example.test/' . uniqid(),
            'external_id' => uniqid(),
            'content_hash' => uniqid(),
            'normalized' => array_merge(['display_name' => 'Jana', 'city' => 'Brno'], $values),
            'images' => [],
            'status' => ScrapeItem::STATUS_PENDING,
        ]);
    }

    // --- rozpoznání napříč poli ------------------------------------------

    public function test_a_known_attribute_value_is_not_reported(): void
    {
        $unknown = app(UnknownValueCollector::class)
            ->unknownValues($this->item(['eye_colour' => 'Modrá', 'hair_colour' => 'Blond']));

        $this->assertTrue($unknown->isEmpty());
    }

    public function test_an_unknown_attribute_value_is_reported_under_its_field(): void
    {
        $unknown = app(UnknownValueCollector::class)
            ->unknownValues($this->item(['eye_colour' => 'Oříšková']));

        $this->assertSame(['eye_colour'], $unknown->keys()->all());
        $this->assertSame(['Oříšková'], $unknown->get('eye_colour')->all());
    }

    public function test_spelling_does_not_make_a_second_gap(): void
    {
        // Diacritics and case are not a different colour.
        $this->assertTrue(
            app(UnknownValueCollector::class)
                ->unknownValues($this->item(['hair_colour' => 'blond']))
                ->isEmpty()
        );
    }

    public function test_an_unknown_town_is_a_gap_of_its_own(): void
    {
        $unknown = app(UnknownValueCollector::class)
            ->unknownValues($this->item(['city' => 'Nikdejov']));

        $this->assertSame(['Nikdejov'], $unknown->get('city')->all());
    }

    public function test_gaps_in_several_fields_are_reported_together(): void
    {
        $unknown = app(UnknownValueCollector::class)->unknownValues($this->item([
            'eye_colour' => 'Modrozelená',
            'pubic_hair' => 'Něco jiného',
        ]));

        $this->assertSame(['eye_colour', 'pubic_hair'], $unknown->keys()->sort()->values()->all());
    }

    public function test_the_summary_names_the_field(): void
    {
        $summary = app(UnknownValueCollector::class)
            ->unknownSummary($this->item(['eye_colour' => 'Oříšková']));

        $this->assertSame(['Barva očí: Oříšková'], $summary->all());
    }

    // --- doplnění do číselníku -------------------------------------------

    public function test_approving_creates_the_option_in_the_right_list(): void
    {
        app(UnknownValueCollector::class)->collect($this->item(['eye_colour' => 'Oříšková']));

        $option = ScrapeUnknownValue::query()->pending()->sole()->approve();

        $this->assertInstanceOf(ProfileAttributeOption::class, $option);
        $this->assertSame('eye_colour', $option->attribute);
        $this->assertSame('Oříšková', $option->getTranslation('label', 'cs'));
        $this->assertTrue(ProfileAttributeOption::knows('eye_colour', 'oriskova'));
    }

    public function test_an_option_that_already_matches_is_not_created_twice(): void
    {
        app(UnknownValueCollector::class)->collect($this->item(['hair_colour' => 'Blond']));

        // Noted before the seeder ran in an earlier deployment; approving it
        // must adopt the existing row rather than make a second "Blond".
        $row = ScrapeUnknownValue::query()->pending()->first();

        if ($row) {
            $row->approve();
        }

        $this->assertSame(
            1,
            ProfileAttributeOption::query()->forAttribute('hair_colour')->where('normalized', 'blond')->count()
        );
    }

    public function test_approving_releases_the_item_that_was_waiting(): void
    {
        $collector = app(UnknownValueCollector::class);
        $item = $this->item(['eye_colour' => 'Oříšková']);
        $collector->collect($item);

        $this->assertFalse($collector->isComplete($item));

        ScrapeUnknownValue::query()->pending()->sole()->approve();
        $collector->forget();

        $this->assertTrue($collector->isComplete($item->fresh()));
        $this->assertTrue($collector->unblockedItems()->contains('id', $item->id));
    }

    // --- uložení na profil -----------------------------------------------

    /**
     * Eight fields were scraped and thrown away because the importer had no
     * home for them. This is the check that they arrive.
     */
    public function test_the_importer_stores_the_detail_fields(): void
    {
        $item = $this->item([
            'eye_colour' => 'Modrá',
            'hair_colour' => 'Blond',
            'hair_length' => 'Dlouhá',
            'bust_type' => 'Přírodní',
            'pubic_hair' => 'Oholené',
            'travels' => 'Evropa',
        ]);
        $item->update(['status' => ScrapeItem::STATUS_APPROVED]);

        $profile = app(ScrapeItemImporter::class)->import($item, false);

        $this->assertSame('Modrá', $profile->content['eye_colour']);
        $this->assertSame('Blond', $profile->content['hair_colour']);
        $this->assertSame('Dlouhá', $profile->content['hair_length']);
        $this->assertSame('Přírodní', $profile->content['bust_type']);
        $this->assertSame('Oholené', $profile->content['pubic_hair']);
        $this->assertSame('Evropa', $profile->content['travels']);
    }

    /**
     * An unknown value must not land on the profile: the admin's select would
     * show a blank where the profile has a value, and the next save would wipe
     * it. It waits in the queue and arrives on the resync instead.
     */
    public function test_a_value_the_catalogue_does_not_know_is_not_stored_yet(): void
    {
        $collector = app(UnknownValueCollector::class);
        $item = $this->item(['eye_colour' => 'Oříšková', 'hair_colour' => 'Blond']);
        $collector->collect($item);
        $item->update(['status' => ScrapeItem::STATUS_APPROVED]);

        $profile = app(ScrapeItemImporter::class)->import($item->fresh(), false);

        $this->assertArrayNotHasKey('eye_colour', $profile->content);
        $this->assertSame('Blond', $profile->content['hair_colour']);

        // …and once somebody adds it, the profile gets it without a re-import.
        ScrapeUnknownValue::query()->pending()->sole()->approve();
        $collector->forget();
        app(ScrapeItemImporter::class)->resync($item->fresh());

        $this->assertSame('Oříšková', $profile->fresh()->content['eye_colour']);
    }

    public function test_free_text_fields_pass_through_without_a_catalogue(): void
    {
        $item = $this->item(['nationality' => 'CZ', 'languages' => 'čeština, angličtina']);
        $item->update(['status' => ScrapeItem::STATUS_APPROVED]);

        $profile = app(ScrapeItemImporter::class)->import($item, false);

        $this->assertSame('CZ', $profile->content['nationality']);
        $this->assertSame('čeština, angličtina', $profile->content['languages']);
    }

    public function test_resync_fills_a_profile_imported_before_the_field_existed(): void
    {
        $item = $this->item(['eye_colour' => 'Modrá']);
        $item->update(['status' => ScrapeItem::STATUS_APPROVED]);

        $profile = app(ScrapeItemImporter::class)->import($item, false);

        // As if it had been imported by the old code, which had nowhere to put
        // the colour.
        $profile->forceFill(['content' => ['card_height_cm' => 170]])->save();

        app(ScrapeItemImporter::class)->resync($item->fresh());

        $this->assertSame('Modrá', $profile->fresh()->content['eye_colour']);
        $this->assertSame(170, $profile->fresh()->content['card_height_cm']);
    }

    public function test_resync_does_not_overwrite_what_somebody_edited(): void
    {
        $item = $this->item(['eye_colour' => 'Modrá']);
        $item->update(['status' => ScrapeItem::STATUS_APPROVED]);

        $profile = app(ScrapeItemImporter::class)->import($item, false);
        $profile->forceFill(['content' => array_merge($profile->content, ['eye_colour' => 'Zelená'])])->save();

        app(ScrapeItemImporter::class)->resync($item->fresh());

        $this->assertSame('Zelená', $profile->fresh()->content['eye_colour']);
    }

    // --- číselník v administraci -----------------------------------------

    public function test_the_form_offers_bust_sizes_from_the_catalogue(): void
    {
        ProfileAttributeOption::create([
            'attribute' => 'bust_size',
            'label' => ['cs' => 'I', 'en' => 'I'],
            'sort_order' => 90,
            'is_active' => true,
        ]);

        $this->assertContains('I', array_keys(ProfileAttributeOption::optionsFor('bust_size')));
    }

    public function test_a_deactivated_option_stops_being_offered(): void
    {
        ProfileAttributeOption::query()
            ->forAttribute('eye_colour')
            ->where('normalized', 'seda')
            ->update(['is_active' => false]);

        $this->assertFalse(ProfileAttributeOption::knows('eye_colour', 'Šedá'));
        $this->assertArrayNotHasKey('Šedá', ProfileAttributeOption::optionsFor('eye_colour'));
    }

    public function test_every_attribute_list_has_options_after_seeding(): void
    {
        foreach (array_keys(ProfileAttributeOption::ATTRIBUTES) as $attribute) {
            $this->assertNotEmpty(
                ProfileAttributeOption::optionsFor($attribute),
                "Číselník {$attribute} je prázdný."
            );
        }
    }

    public function test_the_seeder_creates_no_duplicate_names(): void
    {
        $this->seed(ProfileAttributeOptionSeeder::class);

        $duplicates = ProfileAttributeOption::query()
            ->selectRaw('attribute, normalized, COUNT(*) as total')
            ->groupBy('attribute', 'normalized')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $this->assertTrue($duplicates->isEmpty(), 'Číselník obsahuje duplicity.');
    }
}
