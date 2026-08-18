<?php

namespace Tests\Feature;

use App\Filament\Resources\ScrapeSources\Concerns\HandlesGuidedSettings;
use App\Models\ScrapeSource;
use App\Services\Scraping\SourceConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Přenositelnost nastavení zdroje.
 *
 * Zprovoznit web je ta drahá část scrapování — půl dne hledání selektorů, které
 * nikdo nechce opakovat na testovacím serveru a znovu po obnově ze zálohy.
 */
class ScrapeSourceConfigTest extends TestCase
{
    use RefreshDatabase, HandlesGuidedSettings;

    private function source(): ScrapeSource
    {
        $source = ScrapeSource::create([
            'name' => 'Příklad',
            'slug' => 'priklad',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'schedule_hours' => 12,
            'settings' => ['crawl_delay' => 7, 'discovery' => 'sitemap'],
            'notes' => 'Poznámka k webu.',
        ]);

        $source->fieldMaps()->create([
            'target_field' => 'display_name',
            'selector' => 'h1',
            'extract' => 'text',
            'is_required' => true,
            'sort_order' => 0,
        ]);

        $source->fieldMaps()->create([
            'target_field' => 'city',
            'selector' => '.city',
            'extract' => 'text',
            'sort_order' => 1,
        ]);

        return $source;
    }

    public function test_a_source_survives_a_round_trip(): void
    {
        $json = app(SourceConfig::class)->toJson($this->source());

        ScrapeSource::query()->delete();

        $imported = app(SourceConfig::class)->importJson($json);

        $this->assertSame('Příklad', $imported->name);
        $this->assertSame('https://example.test', $imported->base_url);
        $this->assertSame(7, $imported->settings['crawl_delay']);
        $this->assertSame('sitemap', $imported->settings['discovery']);
        $this->assertSame(12, $imported->schedule_hours);
        $this->assertSame(2, $imported->fieldMaps()->count());
        $this->assertSame('h1', $imported->fieldMaps()->where('target_field', 'display_name')->value('selector'));
    }

    /** Načtený zdroj je návrh, ne provoz: nesmí se sám rozeběhnout. */
    public function test_an_imported_source_arrives_switched_off(): void
    {
        $json = app(SourceConfig::class)->toJson($this->source());

        ScrapeSource::query()->delete();

        $imported = app(SourceConfig::class)->importJson($json);

        $this->assertFalse($imported->is_enabled);
        $this->assertNull($imported->next_run_at);
        $this->assertFalse($imported->isScheduled());
    }

    /** Dvě varianty téhož webu vedle sebe. */
    public function test_a_different_slug_creates_a_second_source(): void
    {
        $json = app(SourceConfig::class)->toJson($this->source());

        app(SourceConfig::class)->importJson($json, 'priklad-test');

        $this->assertSame(2, ScrapeSource::count());
        $this->assertNotNull(ScrapeSource::where('slug', 'priklad-test')->first());
    }

    /** Opakovaný import stejného zdroje nesmí selektory zdvojit. */
    public function test_re_importing_replaces_the_selectors_instead_of_adding_them(): void
    {
        $json = app(SourceConfig::class)->toJson($this->source());

        app(SourceConfig::class)->importJson($json);
        app(SourceConfig::class)->importJson($json);

        $this->assertSame(1, ScrapeSource::count());
        $this->assertSame(2, ScrapeSource::first()->fieldMaps()->count());
    }

    public function test_a_file_from_another_version_is_refused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(SourceConfig::class)->import(['version' => 99, 'source' => ['base_url' => 'https://example.test']]);
    }

    public function test_nonsense_is_refused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(SourceConfig::class)->importJson('tohle není json');
    }

    /**
     * Vedená pole a syrový editor musí ukazovat na totéž místo, jinak by se
     * jedno nastavení dalo měnit na dvou místech a vyhrálo by to poslední.
     */
    public function test_guided_settings_move_between_the_form_and_the_json(): void
    {
        $unpacked = $this->unpackGuidedSettings([
            'settings' => [
                'crawl_delay' => 7,
                'discovery' => 'sitemap',
                'auto_pause' => false,
            ],
        ]);

        $this->assertSame('sitemap', $unpacked['discovery']);
        $this->assertFalse($unpacked['auto_pause']);
        // Ze syrového editoru zmizely, dokud je formulář otevřený.
        $this->assertArrayNotHasKey('discovery', $unpacked['settings']);
        $this->assertSame(['crawl_delay' => 7], $unpacked['settings']);

        $packed = $this->packGuidedSettings($unpacked);

        $this->assertSame('sitemap', $packed['settings']['discovery']);
        $this->assertFalse($packed['settings']['auto_pause']);
        $this->assertSame(7, $packed['settings']['crawl_delay']);
        $this->assertArrayNotHasKey('discovery', $packed);
    }

    /** Prázdné pole znamená „výchozí hodnota", ne „ulož prázdno". */
    public function test_an_empty_guided_field_leaves_no_key_behind(): void
    {
        $packed = $this->packGuidedSettings([
            'settings' => ['proxy' => 'http://stara.proxy:8080'],
            'proxy' => '',
            'sitemap_url' => null,
        ]);

        $this->assertArrayNotHasKey('proxy', $packed['settings']);
        $this->assertArrayNotHasKey('sitemap_url', $packed['settings']);
    }
}
