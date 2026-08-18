<?php

namespace Tests\Feature;

use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Services\Scraping\EmbeddedJson;
use App\Services\Scraping\ScrapeRunner;
use App\Services\Scraping\SiteProbe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Weby, které se skládají až v prohlížeči.
 *
 * Stránka postavená v Reactu přijde jako prázdná skořápka: selektory nenajdou
 * nic a poctivá odpověď byla „tenhle web scraper přečíst neumí". Jenže
 * skořápka prázdná není — aby data nemusela stahovat dvakrát, veze si je
 * s sebou. Ta kopie je navíc lepší než cokoli, co by selektor vydoloval: je to
 * vlastní datová struktura webu, která se s designem nehýbe.
 */
class ScrapeClientRenderedTest extends TestCase
{
    use RefreshDatabase;

    private function nextPage(): string
    {
        return '<html><head><title>Katalog</title></head><body><div id="app"></div>'
            . '<script src="/static/app.js"></script>'
            . '<script id="__NEXT_DATA__" type="application/json">'
            . json_encode([
                'props' => [
                    'pageProps' => [
                        'profile' => [
                            'name' => 'Kristýna',
                            'age' => 24,
                            'address' => ['city' => 'Brno'],
                            'languages' => ['čeština', 'angličtina'],
                        ],
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE)
            . '</script></body></html>';
    }

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

        foreach ([
            ['display_name', 'json:props.pageProps.profile.name', false],
            ['city', 'json:props.pageProps.profile.address.city', false],
            ['languages', 'json:props.pageProps.profile.languages', true],
        ] as $index => [$field, $selector, $multiple]) {
            $source->fieldMaps()->create([
                'target_field' => $field,
                'selector' => $selector,
                'extract' => 'text',
                'multiple' => $multiple,
                'sort_order' => $index,
            ]);
        }

        return $source;
    }

    public function test_values_are_read_out_of_the_embedded_json(): void
    {
        $json = new EmbeddedJson();
        $html = $this->nextPage();

        $this->assertSame('Kristýna', $json->value($html, 'json:props.pageProps.profile.name'));
        $this->assertSame('Brno', $json->value($html, 'json:props.pageProps.profile.address.city'));
        $this->assertSame(['čeština', 'angličtina'], $json->value($html, 'json:props.pageProps.profile.languages'));
    }

    public function test_a_client_rendered_page_is_recognised(): void
    {
        $json = new EmbeddedJson();

        $this->assertTrue($json->looksClientRendered($this->nextPage()));
        $this->assertFalse($json->looksClientRendered(
            '<html><body><h1>Kristýna</h1><p>' . str_repeat('Text o profilu. ', 60) . '</p></body></html>',
        ));
    }

    /** A celá cesta: web, který by jinak nešel přečíst vůbec. */
    public function test_a_whole_profile_is_scraped_from_a_react_page(): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">A</a>',
                200,
                ['Content-Type' => 'text/html'],
            ),
            'https://example.test/p/1' => Http::response($this->nextPage(), 200, ['Content-Type' => 'text/html']),
        ]);

        app(ScrapeRunner::class)->run($this->source(), ['pages' => 1]);

        $item = ScrapeItem::first();

        $this->assertSame('Kristýna', $item->normalized['display_name']);
        $this->assertSame('Brno', $item->normalized['city']);
        $this->assertSame(['čeština', 'angličtina'], $item->normalized['languages']);
    }

    public function test_the_probe_says_why_the_selectors_find_nothing(): void
    {
        Http::fake([
            'https://example.test/sitemap*' => Http::response('nope', 404),
            'https://example.test/list' => Http::response($this->nextPage(), 200, ['Content-Type' => 'text/html']),
        ]);

        $report = app(SiteProbe::class)->run($this->source());

        $this->assertTrue($report['client_rendered']);
        $this->assertContains('json:props.pageProps.profile.name', $report['embedded']);
        $this->assertStringContainsString('skládá až v prohlížeči', implode(' ', $report['notes']));
    }

    /** window.__NUXT__ a spol. jsou tentýž trik jiným jménem. */
    public function test_a_state_assigned_to_window_is_read_too(): void
    {
        $html = '<html><body><div id="app"></div><script>window.__NUXT__ = '
            . '{"data":{"profile":{"name":"Tereza"}}};</script><script src="/a.js"></script></body></html>';

        $this->assertSame('Tereza', (new EmbeddedJson())->value($html, 'json:data.profile.name'));
    }

    /** Rozbitý blok nesmí sebrat ten funkční. */
    public function test_a_broken_block_is_skipped(): void
    {
        $html = '<script type="application/json">{ tohle není json </script>'
            . '<script type="application/json">{"profile":{"name":"Lucie"}}</script>';

        $this->assertSame('Lucie', (new EmbeddedJson())->value($html, 'json:profile.name'));
    }

    /**
     * Externí renderer: adresa jde přes službu, kterou má provozovatel po ruce,
     * a zbytek scraperu o tom neví.
     */
    public function test_the_render_endpoint_is_used_when_set(): void
    {
        Http::fake([
            'https://render.test/*' => Http::response('<h1>Vykresleno</h1>', 200, ['Content-Type' => 'text/html']),
        ]);

        $source = $this->source(['render_endpoint' => 'https://render.test/html?token=abc']);

        $html = app(\App\Services\Scraping\HttpFetcher::class)->get($source, 'https://example.test/p/1');

        $this->assertStringContainsString('Vykresleno', $html);

        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://render.test/html')
            && str_contains($request->url(), urlencode('https://example.test/p/1')));
    }

    public function test_the_placeholder_form_of_the_endpoint_works(): void
    {
        Http::fake(['https://render.test/*' => Http::response('<h1>Vykresleno</h1>', 200)]);

        $source = $this->source(['render_endpoint' => 'https://render.test/{url}/html']);

        app(\App\Services\Scraping\HttpFetcher::class)->get($source, 'https://example.test/p/1');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/html'));
    }
}
