<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\ScrapeFieldMap;
use App\Models\ScrapeItem;
use App\Models\ScrapeRun;
use App\Models\ScrapeSource;
use App\Services\Scraping\FieldExtractor;
use App\Services\Scraping\RobotsTxt;
use App\Services\Scraping\ScrapeItemImporter;
use App\Services\Scraping\ScrapeRunner;
use App\Services\Scraping\Transformers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ScraperTest extends TestCase
{
    use RefreshDatabase;

    private function source(array $settings = []): ScrapeSource
    {
        return ScrapeSource::create([
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => array_merge([
                'crawl_delay' => 0,
                'listing_path' => '/list',
                'detail_link_selector' => 'a.profile',
                'external_id_pattern' => '#/(\d+)/?$#',
                'image_selector' => 'a.gallery',
                'image_attribute' => 'href',
            ], $settings),
        ]);
    }

    // --- robots.txt -------------------------------------------------------

    public function test_it_reads_crawl_delay_and_disallow_rules(): void
    {
        $robots = RobotsTxt::parse("User-agent: *\nCrawl-delay: 5\nDisallow: /private\n", 'TestBot/1.0');

        $this->assertSame(5.0, $robots->crawlDelay);
        $this->assertFalse($robots->allows('/private/page'));
        $this->assertTrue($robots->allows('/public/page'));
    }

    public function test_a_specific_agent_group_wins_over_the_wildcard(): void
    {
        $body = "User-agent: *\nDisallow: /\n\nUser-agent: TestBot\nDisallow: /admin\n";

        $robots = RobotsTxt::parse($body, 'TestBot/1.0');

        $this->assertTrue($robots->allows('/anything'));
        $this->assertFalse($robots->allows('/admin/x'));
    }

    public function test_the_site_delay_wins_when_it_is_longer_than_ours(): void
    {
        $source = $this->source(['crawl_delay' => 2]);
        $source->robots_rules = ['crawl_delay' => 5.0];

        $this->assertSame(5.0, $source->effectiveCrawlDelay());
    }

    public function test_our_delay_wins_when_it_is_longer_than_the_site_asks(): void
    {
        $source = $this->source(['crawl_delay' => 10]);
        $source->robots_rules = ['crawl_delay' => 5.0];

        $this->assertSame(10.0, $source->effectiveCrawlDelay());
    }

    // --- extraction -------------------------------------------------------

    public function test_it_extracts_fields_through_selectors_and_transforms(): void
    {
        $source = $this->source();

        $maps = collect([
            new ScrapeFieldMap([
                'target_field' => 'display_name',
                'selector' => 'h1',
                'extract' => 'text',
                'transforms' => ['collapse_whitespace'],
            ]),
            new ScrapeFieldMap([
                'target_field' => 'card_height_cm',
                'selector' => 'table tr',
                'extract' => 'text',
                'multiple' => true,
                'transforms' => [['regex', '/(\d{3})\s*cm/u'], 'compact', 'first', 'int'],
            ]),
        ]);

        $html = '<html><body><h1>  Anna   Nováková </h1>'
            . '<table><tr><td>Věk 25</td></tr><tr><td>Výška 172 cm</td></tr></table>'
            . '</body></html>';

        $result = (new FieldExtractor())->extract($html, $maps, $source);

        $this->assertSame('Anna Nováková', $result['values']['display_name']);
        $this->assertSame(172, $result['values']['card_height_cm']);
        $this->assertSame([], $result['missing']);
    }

    public function test_a_required_field_that_finds_nothing_is_reported(): void
    {
        $source = $this->source();

        $maps = collect([
            new ScrapeFieldMap([
                'target_field' => 'display_name',
                'selector' => '.nope',
                'extract' => 'text',
                'is_required' => true,
            ]),
        ]);

        $result = (new FieldExtractor())->extract('<html><body></body></html>', $maps, $source);

        $this->assertSame(['display_name'], $result['missing']);
        $this->assertArrayNotHasKey('display_name', $result['values']);
    }

    public function test_compact_drops_empties_before_first_picks_a_value(): void
    {
        $transformers = new Transformers();

        $value = $transformers->apply([null, '', '168'], ['compact', 'first', 'int']);

        $this->assertSame(168, $value);
    }

    public function test_an_invalid_selector_does_not_blow_up_the_run(): void
    {
        $source = $this->source();

        $maps = collect([
            new ScrapeFieldMap([
                'target_field' => 'display_name',
                'selector' => '<<< not a selector >>>',
                'extract' => 'text',
            ]),
        ]);

        $result = (new FieldExtractor())->extract('<html><body><h1>x</h1></body></html>', $maps, $source);

        $this->assertSame([], $result['values']);
    }

    // --- running ----------------------------------------------------------

    private function fakeSite(): void
    {
        Http::fake([
            'example.test/robots.txt' => Http::response("User-agent: *\nCrawl-delay: 0\n"),
            'example.test/list/' => Http::response(
                '<html><body><a class="profile" href="/p/1/">A</a><a class="profile" href="/p/2/">B</a></body></html>'
            ),
            'example.test/p/1/' => Http::response(
                '<html><body><h1>Anna</h1><a class="gallery" href="/img/a.jpg">x</a></body></html>'
            ),
            'example.test/p/2/' => Http::response('<html><body><h1>Bára</h1></body></html>'),
            '*' => Http::response('', 404),
        ]);
    }

    public function test_a_run_stages_items_without_creating_profiles(): void
    {
        $this->fakeSite();
        $source = $this->source();
        $source->fieldMaps()->create([
            'target_field' => 'display_name',
            'selector' => 'h1',
            'extract' => 'text',
            'is_required' => true,
        ]);

        $run = app(ScrapeRunner::class)->run($source, ['pages' => 1]);

        $this->assertSame(ScrapeRun::STATUS_COMPLETED, $run->status);
        $this->assertSame(2, $run->items_new);
        $this->assertSame(2, ScrapeItem::count());
        $this->assertSame(0, Profile::count());
        $this->assertSame(ScrapeItem::STATUS_PENDING, ScrapeItem::first()->status);
    }

    public function test_rerunning_does_not_duplicate_unchanged_items(): void
    {
        $this->fakeSite();
        $source = $this->source();
        $source->fieldMaps()->create(['target_field' => 'display_name', 'selector' => 'h1', 'extract' => 'text']);

        app(ScrapeRunner::class)->run($source, ['pages' => 1]);
        $second = app(ScrapeRunner::class)->run($source, ['pages' => 1]);

        $this->assertSame(2, ScrapeItem::count());
        $this->assertSame(0, $second->items_new);
        $this->assertSame(0, $second->items_updated);
    }

    public function test_a_rejected_item_is_not_put_back_in_the_queue(): void
    {
        $this->fakeSite();
        $source = $this->source();
        $source->fieldMaps()->create(['target_field' => 'display_name', 'selector' => 'h1', 'extract' => 'text']);

        app(ScrapeRunner::class)->run($source, ['pages' => 1]);

        $item = ScrapeItem::first();
        $item->update(['status' => ScrapeItem::STATUS_REJECTED, 'content_hash' => 'stale']);

        app(ScrapeRunner::class)->run($source, ['pages' => 1]);

        $this->assertSame(ScrapeItem::STATUS_REJECTED, $item->fresh()->status);
    }

    public function test_a_dry_run_writes_nothing(): void
    {
        $this->fakeSite();
        $source = $this->source();
        $source->fieldMaps()->create(['target_field' => 'display_name', 'selector' => 'h1', 'extract' => 'text']);

        app(ScrapeRunner::class)->run($source, ['pages' => 1, 'dry_run' => true]);

        $this->assertSame(0, ScrapeItem::count());
    }

    // --- import -----------------------------------------------------------

    public function test_only_an_approved_item_can_be_imported(): void
    {
        $source = $this->source();
        $item = ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/p/1/',
            'external_id' => '1',
            'normalized' => ['display_name' => 'Anna'],
            'status' => ScrapeItem::STATUS_PENDING,
        ]);

        $this->expectExceptionMessage('Importovat lze jen schválenou položku.');

        app(ScrapeItemImporter::class)->import($item, withImages: false);
    }

    public function test_an_imported_profile_is_never_published(): void
    {
        $source = $this->source();
        $item = ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/p/1/',
            'external_id' => '1',
            'normalized' => ['display_name' => 'Anna', 'city' => 'Brno', 'country_code' => 'cz'],
            'status' => ScrapeItem::STATUS_APPROVED,
        ]);

        $profile = app(ScrapeItemImporter::class)->import($item, withImages: false);

        $this->assertFalse($profile->is_public);
        $this->assertSame('pending', $profile->status);
        $this->assertSame('CZ', $profile->country_code);
        $this->assertSame(ScrapeItem::STATUS_IMPORTED, $item->fresh()->status);
        $this->assertSame($profile->id, $item->fresh()->imported_profile_id);
    }
}
