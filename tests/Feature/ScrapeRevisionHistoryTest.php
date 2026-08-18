<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\ScrapeItem;
use App\Models\ScrapeItemRevision;
use App\Models\ScrapeSource;
use App\Models\User;
use App\Services\Scraping\RevisionRecorder;
use App\Services\Scraping\ScrapeRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Co se na zdroji změnilo mezi dvěma běhy.
 *
 * Opakovaný scrape přepsal položku na místě. Počitadlo řeklo „aktualizováno" a
 * tím to skončilo: které pole se hnulo, z čeho na co, jestli přibyla nebo
 * zmizela fotka — všechno pryč v okamžiku zápisu. To je rozdíl mezi katalogem
 * a momentkou.
 */
class ScrapeRevisionHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function source(): ScrapeSource
    {
        $source = ScrapeSource::create([
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => [
                'crawl_delay' => 0,
                'listing_path' => '/list',
                'detail_link_selector' => 'a.profile',
                'respect_robots' => false,
                'conditional_requests' => false,
                'image_selector' => 'a.foto',
                'image_attribute' => 'href',
            ],
        ]);

        foreach ([
            ['display_name', 'h1'],
            ['price_hour', '.cena'],
            ['about_me', '.popis'],
        ] as $index => [$field, $selector]) {
            $source->fieldMaps()->create([
                'target_field' => $field,
                'selector' => $selector,
                'extract' => 'text',
                'sort_order' => $index,
            ]);
        }

        return $source;
    }

    private function detail(string $price, string $about = 'Popis', array $photos = ['/1.jpg']): string
    {
        $gallery = implode('', array_map(
            fn (string $url) => '<a class="foto" href="' . $url . '"></a>',
            $photos,
        ));

        return '<h1>Kristýna</h1><div class="cena">' . $price . '</div>'
            . '<div class="popis">' . $about . '</div>' . $gallery;
    }

    private function fake(string $first, string $second): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">A</a>',
                200,
                ['Content-Type' => 'text/html'],
            ),
            'https://example.test/p/1' => Http::sequence()
                ->push($first, 200, ['Content-Type' => 'text/html'])
                ->push($second, 200, ['Content-Type' => 'text/html']),
        ]);
    }

    public function test_a_changed_field_is_recorded_with_both_values(): void
    {
        $this->fake($this->detail('3000'), $this->detail('6000'));

        $source = $this->source();
        $runner = app(ScrapeRunner::class);

        $runner->run($source, ['pages' => 1]);
        $this->assertSame(0, ScrapeItemRevision::count(), 'První stažení není změna.');

        $runner->run($source->fresh(), ['pages' => 1]);

        $revision = ScrapeItemRevision::first();

        $this->assertNotNull($revision);
        $this->assertSame('3000', $revision->changes['price_hour']['from']);
        $this->assertSame('6000', $revision->changes['price_hour']['to']);
    }

    /** Cena, kontakt nebo jméno je něco jiného než přepsaný popis. */
    public function test_a_price_change_is_flagged_as_notable(): void
    {
        $this->fake($this->detail('3000'), $this->detail('6000'));

        $source = $this->source();
        app(ScrapeRunner::class)->run($source, ['pages' => 1]);
        app(ScrapeRunner::class)->run($source->fresh(), ['pages' => 1]);

        $this->assertTrue(ScrapeItemRevision::first()->is_notable);
    }

    public function test_a_rewritten_description_is_recorded_but_not_flagged(): void
    {
        $this->fake($this->detail('3000', 'Původní popis'), $this->detail('3000', 'Nový popis'));

        $source = $this->source();
        app(ScrapeRunner::class)->run($source, ['pages' => 1]);
        app(ScrapeRunner::class)->run($source->fresh(), ['pages' => 1]);

        $revision = ScrapeItemRevision::first();

        $this->assertNotNull($revision);
        $this->assertFalse($revision->is_notable);
    }

    public function test_added_and_removed_photos_are_recorded(): void
    {
        $this->fake(
            $this->detail('3000', 'Popis', ['/1.jpg', '/2.jpg']),
            $this->detail('3000', 'Popis', ['/2.jpg', '/3.jpg']),
        );

        $source = $this->source();
        app(ScrapeRunner::class)->run($source, ['pages' => 1]);
        app(ScrapeRunner::class)->run($source->fresh(), ['pages' => 1]);

        $revision = ScrapeItemRevision::first();

        $this->assertSame(['https://example.test/3.jpg'], $revision->images_added);
        $this->assertSame(['https://example.test/1.jpg'], $revision->images_removed);
    }

    /** Beze změny se nezakládá nic — historie je seznam událostí, ne návštěv. */
    public function test_an_unchanged_page_writes_no_history(): void
    {
        $this->fake($this->detail('3000'), $this->detail('3000'));

        $source = $this->source();
        app(ScrapeRunner::class)->run($source, ['pages' => 1]);
        app(ScrapeRunner::class)->run($source->fresh(), ['pages' => 1]);

        $this->assertSame(0, ScrapeItemRevision::count());
    }

    /**
     * Web, který mezi dvěma načteními přehodí pořadí služeb, nezměnil nic.
     * Hlásit to každou noc by pohřbilo změny, na kterých záleží.
     */
    public function test_a_reordered_list_is_not_a_change(): void
    {
        $recorder = new RevisionRecorder();

        $diff = $recorder->diff(
            ['services' => ['masáž', 'společnice', 'večeře']],
            ['services' => ['večeře', 'masáž', 'společnice']],
        );

        $this->assertSame([], $diff);
    }

    /** Pole, které zmizí, je taky změna. */
    public function test_a_disappearing_field_is_a_change(): void
    {
        $diff = (new RevisionRecorder())->diff(['price_hour' => '3000'], []);

        $this->assertSame('3000', $diff['price_hour']['from']);
        $this->assertNull($diff['price_hour']['to']);
    }

    public function test_a_profile_is_marked_stale_when_the_source_moves_after_the_import(): void
    {
        $source = $this->source();

        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
        ]);

        $item = ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/p/1',
            'external_id' => '1',
            'status' => ScrapeItem::STATUS_IMPORTED,
            'imported_profile_id' => $profile->id,
            'imported_at' => now()->subDays(3),
        ]);

        $this->assertFalse($item->hasChangedSinceImport());

        ScrapeItemRevision::create([
            'scrape_item_id' => $item->id,
            'changes' => ['price_hour' => ['from' => '3000', 'to' => '6000']],
            'is_notable' => true,
        ]);

        $this->assertTrue($item->fresh()->hasChangedSinceImport());
        $this->assertSame(1, ScrapeItem::query()->changedSinceImport()->count());
    }

    /** Změna z doby před importem profil zastaralým nedělá. */
    public function test_a_change_older_than_the_import_does_not_count(): void
    {
        $source = $this->source();

        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
        ]);

        $item = ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/p/1',
            'external_id' => '1',
            'status' => ScrapeItem::STATUS_IMPORTED,
            'imported_profile_id' => $profile->id,
            'imported_at' => now(),
        ]);

        $revision = ScrapeItemRevision::create([
            'scrape_item_id' => $item->id,
            'changes' => ['city' => ['from' => 'Brno', 'to' => 'Praha']],
        ]);

        $revision->forceFill(['created_at' => now()->subDays(2)])->save();

        $this->assertFalse($item->fresh()->hasChangedSinceImport());
        $this->assertSame(0, ScrapeItem::query()->changedSinceImport()->count());
    }
}
