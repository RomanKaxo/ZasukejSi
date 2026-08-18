<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Models\User;
use App\Services\Scraping\AgeGuard;
use App\Services\Scraping\ScrapeItemImporter;
use App\Services\Scraping\ScrapeRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

/**
 * Nic pod osmnáct. Nikdy.
 *
 * Všechno ostatní, co scraper dělá, je otázka kvality — špatná cena je trapná,
 * zastaralý profil otravný. Tohle do té kategorie nepatří, a proto je to
 * pojistka, ne další řádek ve frontě ke kontrole: reviewer proklikávající
 * padesát profilů v jedenáct večer je přesně ten mechanismus, který tu nesmí
 * být poslední obranou.
 */
class ScrapeAgeGuardTest extends TestCase
{
    use RefreshDatabase;

    private function source(array $settings = []): ScrapeSource
    {
        $source = ScrapeSource::create([
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => array_merge([
                'crawl_delay' => 0,
                'listing_path' => '/list',
                'detail_link_selector' => 'a.profile',
                'respect_robots' => false,
                'conditional_requests' => false,
            ], $settings),
        ]);

        foreach ([['display_name', 'h1'], ['age', '.vek']] as $index => [$field, $selector]) {
            $source->fieldMaps()->create([
                'target_field' => $field,
                'selector' => $selector,
                'extract' => 'text',
                'sort_order' => $index,
            ]);
        }

        return $source;
    }

    private function fake(string $age): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">A</a>',
                200,
                ['Content-Type' => 'text/html'],
            ),
            'https://example.test/p/1' => Http::response(
                '<h1>Kristýna</h1><div class="vek">' . $age . '</div>',
                200,
                ['Content-Type' => 'text/html'],
            ),
        ]);
    }

    public function test_an_under_age_profile_never_reaches_the_review_queue(): void
    {
        $this->fake('17');

        app(ScrapeRunner::class)->run($this->source(), ['pages' => 1]);

        $item = ScrapeItem::first();

        $this->assertSame(ScrapeItem::STATUS_REJECTED, $item->status);
        $this->assertStringContainsString('pod hranicí', (string) $item->error);
        $this->assertSame(0, ScrapeItem::query()->where('status', ScrapeItem::STATUS_PENDING)->count());
    }

    public function test_an_adult_profile_passes(): void
    {
        $this->fake('24');

        app(ScrapeRunner::class)->run($this->source(), ['pages' => 1]);

        $this->assertSame(ScrapeItem::STATUS_PENDING, ScrapeItem::first()->status);
    }

    /** Osmnáct je v pořádku — hranice, ne „nad osmnáct". */
    public function test_eighteen_is_allowed(): void
    {
        $this->fake('18');

        app(ScrapeRunner::class)->run($this->source(), ['pages' => 1]);

        $this->assertSame(ScrapeItem::STATUS_PENDING, ScrapeItem::first()->status);
    }

    /** Věk se dá číst z „19 let" stejně jako z „19". */
    public function test_the_age_is_read_out_of_a_sentence(): void
    {
        $this->fake('17 let');

        app(ScrapeRunner::class)->run($this->source(), ['pages' => 1]);

        $this->assertSame(ScrapeItem::STATUS_REJECTED, ScrapeItem::first()->status);
    }

    /** Neuvedený věk není důkaz ničeho a patří do revize, ne do koše. */
    public function test_a_missing_age_is_left_to_review(): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">A</a>',
                200,
                ['Content-Type' => 'text/html'],
            ),
            'https://example.test/p/1' => Http::response('<h1>Kristýna</h1>', 200, ['Content-Type' => 'text/html']),
        ]);

        app(ScrapeRunner::class)->run($this->source(), ['pages' => 1]);

        $this->assertSame(ScrapeItem::STATUS_PENDING, ScrapeItem::first()->status);
    }

    /** Zdroj smí hranici zvednout. */
    public function test_a_source_may_raise_the_minimum(): void
    {
        $this->fake('19');

        app(ScrapeRunner::class)->run($this->source(['minimum_age' => 21]), ['pages' => 1]);

        $this->assertSame(ScrapeItem::STATUS_REJECTED, ScrapeItem::first()->status);
    }

    /** Ale snížit ji nesmí nikdo. Nastavení může jen přitvrdit. */
    public function test_the_floor_cannot_be_lowered(): void
    {
        $this->fake('16');

        app(ScrapeRunner::class)->run($this->source(['minimum_age' => 15]), ['pages' => 1]);

        $this->assertSame(ScrapeItem::STATUS_REJECTED, ScrapeItem::first()->status);
        $this->assertSame(18, (new AgeGuard())->minimumFor(15));
    }

    /**
     * Druhá pojistka: kdyby položku někdo ručně upravil a schválil, import ji
     * pořád odmítne.
     */
    public function test_the_importer_refuses_even_an_approved_item(): void
    {
        $source = $this->source();

        $item = ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/p/1',
            'external_id' => '1',
            'status' => ScrapeItem::STATUS_APPROVED,
            'normalized' => ['display_name' => 'Kristýna', 'age' => 16],
        ]);

        try {
            app(ScrapeItemImporter::class)->import($item, false);
            $this->fail('Import podběžné položky musí selhat.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('pod hranicí', $e->getMessage());
        }

        $this->assertSame(0, Profile::count());
        $this->assertSame(ScrapeItem::STATUS_REJECTED, $item->fresh()->status);
    }

    /** Ani připojení k existujícímu profilu není cesta okolo. */
    public function test_attaching_refuses_too(): void
    {
        $source = $this->source();

        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
        ]);

        $item = ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/p/1',
            'external_id' => '1',
            'status' => ScrapeItem::STATUS_APPROVED,
            'normalized' => ['display_name' => 'Kristýna', 'age' => 17],
        ]);

        $this->expectException(RuntimeException::class);

        app(ScrapeItemImporter::class)->attachTo($item, $profile, false);
    }

    /** Nesmyslná hodnota v poli není věk — a nesmí projít jako „v pořádku". */
    public function test_an_unreadable_age_is_treated_as_not_stated(): void
    {
        $guard = new AgeGuard();

        $this->assertNull($guard->age(['age' => 'neuvedeno']));
        $this->assertNull($guard->age(['age' => '999']));
        $this->assertFalse($guard->isBlocked(['age' => 'neuvedeno']));
    }
}
