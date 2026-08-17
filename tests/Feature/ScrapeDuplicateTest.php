<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Models\User;
use App\Services\Scraping\DuplicateFinder;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The scraper recognised only a repeat of itself: the same `external_id` from
 * the same source. The same woman advertised on a second site, or already a
 * profile here, sailed through and became a duplicate.
 */
class ScrapeDuplicateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    private function source(string $slug = 'a'): ScrapeSource
    {
        return ScrapeSource::create([
            'name' => 'Zdroj ' . $slug,
            'slug' => $slug,
            'base_url' => 'https://' . $slug . '.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => [],
        ]);
    }

    private function item(ScrapeSource $source, array $normalized, ?string $url = null): ScrapeItem
    {
        return ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => $url ?? ('https://' . $source->slug . '.test/' . uniqid()),
            'external_id' => uniqid(),
            'content_hash' => uniqid(),
            'normalized' => $normalized,
            'images' => [],
            'status' => ScrapeItem::STATUS_PENDING,
        ]);
    }

    private function profile(string $name, array $attributes = []): Profile
    {
        return Profile::factory()->create(array_merge([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'display_name' => ['cs' => $name, 'en' => $name],
        ], $attributes));
    }

    // --- normalisation ----------------------------------------------------

    public function test_names_are_compared_without_diacritics_case_or_spacing(): void
    {
        // Two sites will not spell a working name the same way.
        $this->assertSame(
            DuplicateFinder::normalizeName('Anička'),
            DuplicateFinder::normalizeName('ANICKA')
        );
        $this->assertSame(
            DuplicateFinder::normalizeName('Marie K.'),
            DuplicateFinder::normalizeName('marie k')
        );
        $this->assertNotSame(
            DuplicateFinder::normalizeName('Anna'),
            DuplicateFinder::normalizeName('Hanna')
        );
    }

    public function test_a_phone_is_compared_by_its_last_nine_digits(): void
    {
        $this->assertSame(['777123456'], DuplicateFinder::phonesOf(['+420 777 123 456']));
        $this->assertSame(['777123456'], DuplicateFinder::phonesOf(['00420777123456']));
        $this->assertSame(['777123456'], DuplicateFinder::phonesOf([['phone' => '777123456']]));

        // Too short to be a phone: a house number or a price.
        $this->assertSame([], DuplicateFinder::phonesOf(['12345', '2 500 Kč']));
    }

    // --- against existing profiles ---------------------------------------

    public function test_the_same_name_and_city_as_a_profile_is_flagged(): void
    {
        $profile = $this->profile('Anička', ['city' => 'Praha']);
        $item = $this->item($this->source(), ['display_name' => 'ANICKA', 'city' => 'praha']);

        app(DuplicateFinder::class)->check($item);

        $this->assertSame($profile->id, $item->fresh()->duplicate_profile_id);
        $this->assertSame(DuplicateFinder::REASON_NAME_CITY, $item->fresh()->duplicate_reason);
    }

    public function test_a_shared_phone_beats_a_different_name(): void
    {
        $profile = $this->profile('Jiné jméno', ['contacts' => ['phone' => '+420 777 123 456']]);
        $item = $this->item($this->source(), ['display_name' => 'Nikdo', 'phone' => '777 123 456']);

        app(DuplicateFinder::class)->check($item);

        // A phone is the strongest claim there is; the name is a stage name.
        $this->assertSame($profile->id, $item->fresh()->duplicate_profile_id);
        $this->assertSame(DuplicateFinder::REASON_PHONE, $item->fresh()->duplicate_reason);
    }

    public function test_the_same_name_in_another_city_is_flagged_only_weakly(): void
    {
        $profile = $this->profile('Anička', ['city' => 'Brno']);
        $item = $this->item($this->source(), ['display_name' => 'Anička', 'city' => 'Ostrava']);

        app(DuplicateFinder::class)->check($item);

        // Plenty of women share a working name, so this is a hint, not a claim.
        $this->assertSame($profile->id, $item->fresh()->duplicate_profile_id);
        $this->assertSame(DuplicateFinder::REASON_NAME, $item->fresh()->duplicate_reason);
    }

    public function test_an_unrelated_item_is_not_flagged(): void
    {
        $this->profile('Anička', ['city' => 'Praha']);
        $item = $this->item($this->source(), ['display_name' => 'Barbora', 'city' => 'Plzeň']);

        app(DuplicateFinder::class)->check($item);

        $this->assertFalse($item->fresh()->hasDuplicate());
        $this->assertNotNull($item->fresh()->duplicate_checked_at);
    }

    public function test_an_item_is_not_matched_against_the_profile_it_created(): void
    {
        $profile = $this->profile('Anička', ['city' => 'Praha']);
        $item = $this->item($this->source(), ['display_name' => 'Anička', 'city' => 'Praha']);
        $item->forceFill([
            'status' => ScrapeItem::STATUS_IMPORTED,
            'imported_profile_id' => $profile->id,
        ])->save();

        app(DuplicateFinder::class)->check($item);

        $this->assertNull($item->fresh()->duplicate_profile_id);
    }

    // --- across sources ---------------------------------------------------

    public function test_the_same_woman_from_two_sources_is_flagged(): void
    {
        $first = $this->item($this->source('a'), ['display_name' => 'Anička', 'city' => 'Praha']);
        $second = $this->item($this->source('b'), ['display_name' => 'Anicka', 'city' => 'Praha']);

        app(DuplicateFinder::class)->check($second);

        $this->assertSame($first->id, $second->fresh()->duplicate_item_id);
        $this->assertNull($second->fresh()->duplicate_profile_id);
    }

    public function test_a_rejected_item_is_not_raised_again(): void
    {
        $rejected = $this->item($this->source('a'), ['display_name' => 'Anička']);
        $rejected->update(['status' => ScrapeItem::STATUS_REJECTED]);

        $fresh = $this->item($this->source('b'), ['display_name' => 'Anička']);

        app(DuplicateFinder::class)->check($fresh);

        // Rejecting was a decision; dragging it back would undo it.
        $this->assertFalse($fresh->fresh()->hasDuplicate());
    }

    public function test_a_profile_wins_over_another_queue_item(): void
    {
        $profile = $this->profile('Anička', ['city' => 'Praha']);
        $this->item($this->source('a'), ['display_name' => 'Anička', 'city' => 'Praha']);
        $second = $this->item($this->source('b'), ['display_name' => 'Anička', 'city' => 'Praha']);

        app(DuplicateFinder::class)->check($second);

        // A profile already exists on the site, so importing again is the
        // actual mistake.
        $this->assertSame($profile->id, $second->fresh()->duplicate_profile_id);
        $this->assertNull($second->fresh()->duplicate_item_id);
    }

    // --- re-checking ------------------------------------------------------

    public function test_a_corrected_name_clears_the_old_verdict(): void
    {
        $this->profile('Anička', ['city' => 'Praha']);
        $item = $this->item($this->source(), ['display_name' => 'Anička', 'city' => 'Praha']);

        app(DuplicateFinder::class)->check($item);
        $this->assertTrue($item->fresh()->hasDuplicate());

        $item->update(['normalized' => ['display_name' => 'Někdo úplně jiný', 'city' => 'Plzeň']]);
        app(DuplicateFinder::class)->check($item->fresh());

        $this->assertFalse($item->fresh()->hasDuplicate());
    }

    public function test_the_queue_can_be_filtered_to_possible_duplicates(): void
    {
        $this->profile('Anička', ['city' => 'Praha']);

        $flagged = $this->item($this->source('a'), ['display_name' => 'Anička', 'city' => 'Praha']);
        $clean = $this->item($this->source('b'), ['display_name' => 'Barbora', 'city' => 'Plzeň']);

        app(DuplicateFinder::class)->check($flagged);
        app(DuplicateFinder::class)->check($clean);

        $ids = ScrapeItem::query()->possibleDuplicates()->pluck('id')->all();

        $this->assertSame([$flagged->id], $ids);
    }

    public function test_the_label_names_the_match_and_the_reason(): void
    {
        $profile = $this->profile('Anička', ['city' => 'Praha']);
        $item = $this->item($this->source(), ['display_name' => 'Anička', 'city' => 'Praha']);

        app(DuplicateFinder::class)->check($item);

        $label = $item->fresh()->duplicateLabel();

        $this->assertStringContainsString('profil #' . $profile->id, $label);
        $this->assertStringContainsString('shodné jméno i město', $label);
    }
}
