<?php

namespace Tests\Feature;

use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Services\Scraping\PageEncoding;
use App\Services\Scraping\ScrapeRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Stránky, které nejsou v UTF-8.
 *
 * Všechno za stahováním UTF-8 předpokládá — parseru DOMu se to dokonce
 * výslovně říká. Stránka ve windows-1250 proto neselhala, jen tiše zapsala
 * do profilu „Krist??na". České, slovenské a polské weby postavené před
 * koncem nultých let tohle kódování pořád posílají.
 */
class ScrapePageEncodingTest extends TestCase
{
    use RefreshDatabase;

    private function windows1250(string $text): string
    {
        // mbstring tohohle PHP Windows-1250 neumí, iconv ano — což je přesně ten
        // důvod, proč se v převodníku zkouší obojí.
        return (string) iconv('UTF-8', 'Windows-1250', $text);
    }

    public function test_a_declared_windows_1250_page_becomes_utf8(): void
    {
        $body = '<html><head><meta charset="windows-1250"></head><body><h1>'
            . 'Kristýna Nováková, Plzeň</h1></body></html>';

        $converted = (new PageEncoding())->toUtf8($this->windows1250($body));

        $this->assertTrue(mb_check_encoding($converted, 'UTF-8'));
        $this->assertStringContainsString('Kristýna Nováková, Plzeň', $converted);
    }

    /** Hlavička odpovědi má přednost před tím, co říká dokument. */
    public function test_the_content_type_header_wins(): void
    {
        $body = $this->windows1250('<html><body><p>Příliš žluťoučký kůň</p></body></html>');

        $converted = (new PageEncoding())->toUtf8($body, 'text/html; charset=windows-1250');

        $this->assertStringContainsString('Příliš žluťoučký kůň', $converted);
    }

    /** Po převodu nesmí v dokumentu zůstat staré kódování — parser by se vrátil zpět. */
    public function test_the_declaration_is_rewritten_to_utf8(): void
    {
        $body = $this->windows1250('<html><head><meta charset="windows-1250"></head><body>Žofie</body></html>');

        $converted = (new PageEncoding())->toUtf8($body);

        $this->assertStringNotContainsStringIgnoringCase('charset="windows-1250"', $converted);
        $this->assertStringContainsStringIgnoringCase('charset="utf-8"', $converted);
    }

    /** Stránka, která už v UTF-8 je, se nesmí převádět podruhé. */
    public function test_utf8_is_left_alone(): void
    {
        $body = '<html><head><meta charset="utf-8"></head><body><p>Příliš žluťoučký kůň</p></body></html>';

        $this->assertSame($body, (new PageEncoding())->toUtf8($body));
    }

    /** Nikdo nic neřekl a bajty nejsou platné UTF-8 — hádá se, ale ne naslepo. */
    public function test_an_undeclared_page_is_guessed(): void
    {
        $body = $this->windows1250('<html><body><p>Šárka Dvořáková</p></body></html>');

        $converted = (new PageEncoding())->toUtf8($body);

        $this->assertTrue(mb_check_encoding($converted, 'UTF-8'));
        $this->assertStringContainsString('Šárka Dvořáková', $converted);
    }

    /** A celá cesta: co se stáhne ve windows-1250, uloží se čitelné. */
    public function test_a_scraped_profile_keeps_its_diacritics(): void
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
            ],
        ]);

        $source->fieldMaps()->create([
            'target_field' => 'display_name',
            'selector' => 'h1',
            'extract' => 'text',
            'is_required' => true,
            'sort_order' => 0,
        ]);

        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">A</a>',
                200,
                ['Content-Type' => 'text/html'],
            ),
            'https://example.test/p/1' => Http::response(
                $this->windows1250('<html><head><meta charset="windows-1250"></head><body><h1>Kristýna Nováková</h1></body></html>'),
                200,
                ['Content-Type' => 'text/html; charset=windows-1250'],
            ),
        ]);

        app(ScrapeRunner::class)->run($source, ['pages' => 1]);

        $this->assertSame('Kristýna Nováková', ScrapeItem::first()->normalized['display_name']);
    }
}
