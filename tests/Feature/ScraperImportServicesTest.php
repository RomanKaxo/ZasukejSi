<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\ScrapeItem;
use App\Models\ScrapeRun;
use App\Models\ScrapeSource;
use App\Models\Service;
use App\Services\Scraping\ScrapeItemImporter;
use App\Services\Scraping\ScrapeRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScraperImportServicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The importer only stores a value the catalogue knows; bust size has
        // a list behind it now.
        $this->seed(\Database\Seeders\ProfileAttributeOptionSeeder::class);
    }

    private function source(bool $enabled = true): ScrapeSource
    {
        return ScrapeSource::create([
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => $enabled,
            'settings' => ['crawl_delay' => 0],
        ]);
    }

    private function approvedItem(ScrapeSource $source, array $values): ScrapeItem
    {
        return ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/p/1/',
            'external_id' => '1',
            'normalized' => $values,
            'status' => ScrapeItem::STATUS_APPROVED,
        ]);
    }

    public function test_scraped_services_are_linked_to_our_own_catalogue(): void
    {
        $licking = Service::create([
            'name' => ['cs' => 'Lízání', 'en' => 'Licking'],
            'description' => ['cs' => '', 'en' => ''],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $item = $this->approvedItem($this->source(), [
            'display_name' => 'Anna',
            'services' => ['lízání', 'Neexistující služba'],
        ]);

        $profile = app(ScrapeItemImporter::class)->import($item, withImages: false);

        // Matched case-insensitively; the unknown name is ignored rather than
        // silently added to our catalogue.
        $this->assertSame([$licking->id], $profile->services()->pluck('services.id')->all());
        $this->assertSame(1, Service::count());
    }

    public function test_physical_attributes_land_in_the_profile_content(): void
    {
        $item = $this->approvedItem($this->source(), [
            'display_name' => 'Anna',
            'card_height_cm' => 165,
            'weight_kg' => 54,
            'bust_size' => 'C',
            'nationality' => 'Kolumbijská',
            'languages' => 'Angličtina, Španělština',
        ]);

        $profile = app(ScrapeItemImporter::class)->import($item, withImages: false);

        $this->assertSame(165, $profile->height);
        $this->assertSame(54, $profile->weight);
        $this->assertSame('C', $profile->bust_size);
        $this->assertSame('Angličtina, Španělština', $profile->languages);
    }

    public function test_a_disabled_source_cannot_be_harvested(): void
    {
        $run = app(ScrapeRunner::class)->run($this->source(enabled: false), ['pages' => 1]);

        $this->assertSame(ScrapeRun::STATUS_FAILED, $run->status);
        $this->assertStringContainsString('vypnutý', $run->error);
        $this->assertSame(0, ScrapeItem::count());
    }

    public function test_a_disabled_source_can_still_be_examined_with_a_dry_run(): void
    {
        $run = app(ScrapeRunner::class)->run(
            $this->source(enabled: false),
            ['pages' => 1, 'dry_run' => true, 'url' => 'https://example.test/p/1/'],
        );

        $this->assertNotSame(ScrapeRun::STATUS_FAILED, $run->status);
        $this->assertSame(0, Profile::count());
    }
}
