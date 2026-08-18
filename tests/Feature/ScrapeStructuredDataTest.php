<?php

namespace Tests\Feature;

use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Services\Scraping\ScrapeRunner;
use App\Services\Scraping\StructuredData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Data, která web sám zveřejňuje pro vyhledávače a sociální sítě.
 *
 * Každý ručně psaný selektor je odhad o cizím kódu a rozbije se v týdnu, kdy
 * web dostane nový vzhled. JSON-LD a OpenGraph říkají totéž záměrně, v podobě,
 * kterou web sám udržuje — a všude stejně.
 */
class ScrapeStructuredDataTest extends TestCase
{
    use RefreshDatabase;

    private function page(string $extra = ''): string
    {
        return <<<HTML
        <html><head>
            <meta property="og:title" content="Kristýna, Brno">
            <meta name="description" content="Popis profilu.">
            <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "Person",
                "name": "Kristýna",
                "height": "168 cm",
                "knowsLanguage": ["čeština", "angličtina"],
                "address": {"@type": "PostalAddress", "addressLocality": "Brno"},
                "image": ["https://example.test/1.jpg", "https://example.test/2.jpg"]
            }
            </script>
            {$extra}
        </head><body></body></html>
        HTML;
    }

    public function test_values_are_read_from_json_ld(): void
    {
        $data = new StructuredData();

        $this->assertSame('Kristýna', $data->value($this->page(), 'jsonld:name'));
        $this->assertSame('168 cm', $data->value($this->page(), 'jsonld:height'));
        $this->assertSame('Brno', $data->value($this->page(), 'jsonld:address.addressLocality'));
    }

    public function test_a_list_stays_a_list(): void
    {
        $languages = (new StructuredData())->value($this->page(), 'jsonld:knowsLanguage');

        $this->assertSame(['čeština', 'angličtina'], $languages);
    }

    public function test_meta_tags_are_read_too(): void
    {
        $data = new StructuredData();

        $this->assertSame('Kristýna, Brno', $data->value($this->page(), 'meta:og:title'));
        $this->assertSame('Popis profilu.', $data->value($this->page(), 'meta:description'));
    }

    /** Web může mít bloků víc a jeden rozbitý nesmí sebrat ty ostatní. */
    public function test_a_broken_block_does_not_cost_the_working_one(): void
    {
        $html = $this->page('<script type="application/ld+json">{ tohle není json </script>');

        $this->assertSame('Kristýna', (new StructuredData())->value($html, 'jsonld:name'));
    }

    /** `@graph` je běžný obal, jehož členy jsou to podstatné. */
    public function test_a_graph_wrapper_is_unpacked(): void
    {
        $html = '<html><head><script type="application/ld+json">'
            . '{"@context":"https://schema.org","@graph":[{"@type":"WebSite","name":"Katalog"},{"@type":"Person","name":"Tereza"}]}'
            . '</script></head><body></body></html>';

        $names = (new StructuredData())->value($html, 'jsonld:name');

        $this->assertSame(['Katalog', 'Tereza'], $names);
    }

    public function test_the_available_keys_are_listed_for_the_admin(): void
    {
        $keys = (new StructuredData())->availableKeys($this->page());

        $this->assertContains('jsonld:name', $keys);
        $this->assertContains('jsonld:address.addressLocality', $keys);
        $this->assertContains('meta:og:title', $keys);
    }

    /** A hlavně: dá se z toho poskládat celý zdroj bez jediného selektoru. */
    public function test_a_whole_profile_can_be_scraped_without_a_single_selector(): void
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
                'image_selector' => 'jsonld:image',
            ],
        ]);

        foreach ([
            ['display_name', 'jsonld:name', false],
            ['city', 'jsonld:address.addressLocality', false],
            ['languages', 'jsonld:knowsLanguage', true],
        ] as $index => [$field, $selector, $multiple]) {
            $source->fieldMaps()->create([
                'target_field' => $field,
                'selector' => $selector,
                'extract' => 'text',
                'multiple' => $multiple,
                'sort_order' => $index,
            ]);
        }

        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">A</a>',
                200,
                ['Content-Type' => 'text/html'],
            ),
            'https://example.test/p/1' => Http::response($this->page(), 200, ['Content-Type' => 'text/html']),
        ]);

        app(ScrapeRunner::class)->run($source, ['pages' => 1]);

        $item = ScrapeItem::first();

        $this->assertSame('Kristýna', $item->normalized['display_name']);
        $this->assertSame('Brno', $item->normalized['city']);
        $this->assertSame(['čeština', 'angličtina'], $item->normalized['languages']);
        $this->assertSame(
            ['https://example.test/1.jpg', 'https://example.test/2.jpg'],
            $item->images,
        );
    }
}
