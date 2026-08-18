<?php

namespace Tests\Feature;

use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Services\Scraping\HtmlArchive;
use App\Services\Scraping\ScrapeRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use ZipArchive;

/**
 * ZIP se stránkami uloženými z prohlížeče.
 *
 * Těžká část není ZIP. Je to, že uložená stránka neví, odkud je: prohlížeč ji
 * pojmenuje podle titulku, takže „Kristýna — Brno.html" neřekne, o který profil
 * jde. A adresa není ozdoba — rozhoduje o tom, jestli je to nový profil, nebo
 * ten, který už máme.
 */
class ScrapeHtmlArchiveTest extends TestCase
{
    use RefreshDatabase;

    private string $zipPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->zipPath = tempnam(sys_get_temp_dir(), 'archiv_') . '.zip';
    }

    protected function tearDown(): void
    {
        if (is_file($this->zipPath)) {
            @unlink($this->zipPath);
        }

        parent::tearDown();
    }

    /** @param array<string, string> $files */
    private function zip(array $files): string
    {
        $zip = new ZipArchive();
        $zip->open($this->zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }

        $zip->close();

        return $this->zipPath;
    }

    private function page(string $body, string $head = ''): string
    {
        return '<html><head>' . $head . '</head><body>' . $body . '</body></html>';
    }

    private function source(): ScrapeSource
    {
        $source = ScrapeSource::create([
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => ['crawl_delay' => 0, 'respect_robots' => false],
        ]);

        $source->fieldMaps()->create([
            'target_field' => 'display_name',
            'selector' => 'h1',
            'extract' => 'text',
            'is_required' => true,
            'sort_order' => 0,
        ]);

        return $source;
    }

    /** Chrome i Edge píšou adresu na začátek uložené stránky. */
    public function test_the_address_is_read_from_the_saved_from_comment(): void
    {
        $html = '<!-- saved from url=(0033)https://example.test/p/1 -->' . $this->page('<h1>Kristýna</h1>');

        $this->assertSame('https://example.test/p/1', (new HtmlArchive())->urlFrom($html));
    }

    public function test_the_address_is_read_from_the_canonical_link(): void
    {
        $html = $this->page('<h1>Tereza</h1>', '<link rel="canonical" href="https://example.test/p/2">');

        $this->assertSame('https://example.test/p/2', (new HtmlArchive())->urlFrom($html));
    }

    public function test_the_address_is_read_from_open_graph(): void
    {
        $html = $this->page('<h1>Lucie</h1>', '<meta property="og:url" content="https://example.test/p/3">');

        $this->assertSame('https://example.test/p/3', (new HtmlArchive())->urlFrom($html));
    }

    /** Ruční seznam má přednost: někdo ho psal záměrně. */
    public function test_a_manifest_wins_over_what_the_page_says(): void
    {
        $path = $this->zip([
            'profil.html' => $this->page('<h1>Kristýna</h1>', '<link rel="canonical" href="https://example.test/spatna">'),
            'manifest.csv' => "profil.html;https://example.test/p/9\n",
        ]);

        $archive = (new HtmlArchive())->read($path);

        $this->assertCount(1, $archive['pages']);
        $this->assertSame('https://example.test/p/9', $archive['pages'][0]['url']);
    }

    /**
     * Soubor bez adresy se hlásí jménem, nehádá se.
     *
     * Import pod špatnou adresou by tiše přepsal jiný profil, a to je horší
     * než jeden řádek „tuhle jsem nepoznal".
     */
    public function test_a_page_without_an_address_is_reported_not_guessed(): void
    {
        $path = $this->zip([
            'zname.html' => '<!-- saved from url=(0033)https://example.test/p/1 -->' . $this->page('<h1>A</h1>'),
            'nezname.html' => $this->page('<h1>B</h1>'),
        ]);

        $archive = (new HtmlArchive())->read($path);

        $this->assertCount(1, $archive['pages']);
        $this->assertCount(1, $archive['problems']);
        $this->assertStringContainsString('nezname.html', $archive['problems'][0]);
    }

    /** Smetí z macOS a soubory, které nejsou stránky, se ignorují. */
    public function test_junk_is_ignored(): void
    {
        $path = $this->zip([
            '__MACOSX/._profil.html' => 'binární smetí',
            'obrazek.jpg' => 'nejsem stránka',
            'slozka/profil.html' => '<!-- saved from url=(0033)https://example.test/p/1 -->' . $this->page('<h1>A</h1>'),
        ]);

        $archive = (new HtmlArchive())->read($path);

        $this->assertCount(1, $archive['pages']);
        $this->assertSame([], $archive['problems']);
    }

    public function test_a_file_that_is_not_a_zip_is_refused(): void
    {
        file_put_contents($this->zipPath, 'tohle není zip');

        $this->expectExceptionMessageMatches('/ZIP/');

        (new HtmlArchive())->read($this->zipPath);
    }

    /** A celá cesta: z archivu vznikne jeden běh a položky ve frontě. */
    public function test_an_archive_becomes_one_run_with_several_items(): void
    {
        Http::fake();

        $path = $this->zip([
            'a.html' => '<!-- saved from url=(0033)https://example.test/p/1 -->' . $this->page('<h1>Kristýna</h1>'),
            'b.html' => '<!-- saved from url=(0033)https://example.test/p/2 -->' . $this->page('<h1>Tereza</h1>'),
            'c.html' => $this->page('<h1>Lucie</h1>', '<link rel="canonical" href="https://example.test/p/3">'),
        ]);

        $archive = (new HtmlArchive())->read($path);
        $run = app(ScrapeRunner::class)->ingestMany($this->source(), $archive['pages']);

        $this->assertSame(3, $run->items_found);
        $this->assertSame(3, $run->items_new);
        $this->assertSame(3, ScrapeItem::count());

        // Jeden archiv je jedna sklizeň, ne tři.
        $this->assertSame(1, \App\Models\ScrapeRun::count());

        Http::assertNothingSent();
    }

    /** Jedna rozbitá stránka nesmí shodit celou dávku. */
    public function test_one_bad_page_does_not_stop_the_batch(): void
    {
        Http::fake();

        $pages = [
            ['url' => 'https://example.test/p/1', 'html' => $this->page('<h1>Kristýna</h1>')],
            ['url' => '', 'html' => $this->page('<h1>Bez adresy</h1>')],
            ['url' => 'https://example.test/p/3', 'html' => $this->page('<h1>Lucie</h1>')],
        ];

        $run = app(ScrapeRunner::class)->ingestMany($this->source(), $pages);

        $this->assertSame(2, $run->items_new);
        $this->assertSame(1, $run->items_failed);
        $this->assertSame(2, ScrapeItem::count());
    }
}
