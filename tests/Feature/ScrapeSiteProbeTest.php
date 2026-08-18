<?php

namespace Tests\Feature;

use App\Models\ScrapeSource;
use App\Services\Scraping\SiteProbe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Průzkum webu: co by bylo potřeba, aby se z něj dalo stahovat.
 *
 * Zprovoznit zdroj bylo hledání v cizím kódu — otevřít stránku, přečíst
 * markup, odhadnout selektor, spustit zkušební běh, odhadnout znovu. Každé
 * takové kolo stálo běh proti cizímu serveru a odpovědí bylo číslo, ze
 * kterého nešlo poznat, jestli je špatně selektor, stránkování, nebo nás web
 * prostě odmítl.
 */
class ScrapeSiteProbeTest extends TestCase
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
                'listing_path' => '/eskort',
                'respect_robots' => false,
            ], $settings),
        ]);
    }

    private function listing(): string
    {
        return '<html><body>'
            . '<a href="/o-nas">O nás</a>'
            . '<a class="card-link" href="/profil/kristyna-brno">Kristýna</a>'
            . '<a class="card-link" href="/profil/tereza-praha">Tereza</a>'
            . '<a class="card-link" href="/profil/sarka-ostrava">Šárka</a>'
            . '<a class="card-link" href="/profil/lucie-plzen">Lucie</a>'
            . '</body></html>';
    }

    public function test_repeated_links_are_reported_as_a_candidate(): void
    {
        Http::fake([
            'https://example.test/sitemap*' => Http::response('nope', 404),
            'https://example.test/eskort' => Http::response($this->listing(), 200, ['Content-Type' => 'text/html']),
        ]);

        $report = app(SiteProbe::class)->run($this->source());

        $this->assertNotEmpty($report['links']);

        $best = $report['links'][0];

        $this->assertSame(4, $best['count']);
        $this->assertSame('a.card-link', $best['detail_link_selector']);
        $this->assertStringContainsString('profil', $best['shape']);

        // Návrh filtru musí sedět na skutečné adrese, jinak je k ničemu.
        $this->assertSame(1, preg_match($best['detail_url_pattern'], '/profil/kristyna-brno'));
        $this->assertSame(0, preg_match($best['detail_url_pattern'], '/o-nas'));
    }

    /** Jediný odkaz na navigaci není výpis. */
    public function test_one_off_links_are_not_offered(): void
    {
        Http::fake([
            'https://example.test/sitemap*' => Http::response('nope', 404),
            'https://example.test/eskort' => Http::response(
                '<html><body><a href="/kontakt-na-nas">Kontakt</a></body></html>',
                200,
                ['Content-Type' => 'text/html'],
            ),
        ]);

        $report = app(SiteProbe::class)->run($this->source());

        $this->assertSame([], $report['links']);
        $this->assertNotEmpty($report['notes']);
    }

    public function test_a_sitemap_is_found_and_counted(): void
    {
        Http::fake([
            'https://example.test/sitemap.xml' => Http::response(
                '<?xml version="1.0" encoding="UTF-8"?>'
                . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
                . '<url><loc>https://example.test/profil/kristyna-brno</loc></url>'
                . '<url><loc>https://example.test/profil/tereza-praha</loc></url>'
                . '</urlset>',
                200,
                ['Content-Type' => 'application/xml'],
            ),
            'https://example.test/eskort' => Http::response($this->listing(), 200, ['Content-Type' => 'text/html']),
        ]);

        $report = app(SiteProbe::class)->run($this->source());

        $this->assertTrue($report['sitemap']['found']);
        $this->assertSame(2, $report['sitemap']['count']);
        $this->assertNotEmpty($report['notes']);
    }

    public function test_structured_data_keys_are_reported(): void
    {
        Http::fake([
            'https://example.test/sitemap*' => Http::response('nope', 404),
            'https://example.test/eskort' => Http::response(
                '<html><head><meta property="og:title" content="Katalog">'
                . '<script type="application/ld+json">{"@type":"Person","name":"Kristýna"}</script>'
                . '</head><body></body></html>',
                200,
                ['Content-Type' => 'text/html'],
            ),
        ]);

        $report = app(SiteProbe::class)->run($this->source());

        $this->assertContains('jsonld:name', $report['structured']);
        $this->assertContains('meta:og:title', $report['structured']);
    }

    /** Nedostupná stránka je odpověď, ne pád. */
    public function test_a_refused_page_is_reported_not_thrown(): void
    {
        Http::fake([
            'https://example.test/*' => Http::response('Forbidden', 403),
        ]);

        $report = app(SiteProbe::class)->run($this->source());

        $this->assertSame([], $report['links']);
        $this->assertStringContainsString('403', implode(' ', $report['notes']));
    }
}
