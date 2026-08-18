<?php

namespace Tests\Feature;

use App\Models\ScrapeItem;
use App\Models\ScrapeRun;
use App\Models\ScrapeSource;
use App\Services\Scraping\ScrapeRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Stránka, kterou stáhl někdo jiný.
 *
 * Jediná cesta, která nepotřebuje vůbec žádný požadavek. Web může odmítat
 * tenhle server a přitom se k němu člověk u obyčejného prohlížeče dostane bez
 * potíží — tohle mu umožní stránku uložit a předat ji dál.
 *
 * Podstatné je, že se tím nic neobchází: selektory, věková pojistka, vlastní
 * pravidla, kontrola duplicit i fronta ke kontrole jsou úplně stejné. Liší se
 * jen to, odkud HTML přišlo.
 */
class ScrapeIngestHtmlTest extends TestCase
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
                'respect_robots' => false,
                'image_selector' => 'a.foto',
                'image_attribute' => 'href',
            ], $settings),
        ]);

        foreach ([['display_name', 'h1'], ['city', '.mesto'], ['age', '.vek']] as $index => [$field, $selector]) {
            $source->fieldMaps()->create([
                'target_field' => $field,
                'selector' => $selector,
                'extract' => 'text',
                'is_required' => $field === 'display_name',
                'sort_order' => $index,
            ]);
        }

        return $source;
    }

    private function page(string $age = '24', string $city = 'Brno'): string
    {
        return '<html><body><h1>Kristýna</h1><div class="mesto">' . $city . '</div>'
            . '<div class="vek">' . $age . '</div>'
            . '<a class="foto" href="/1.jpg"></a></body></html>';
    }

    public function test_a_pasted_page_becomes_a_queued_item(): void
    {
        // Ani jeden požadavek: to je celý smysl.
        Http::fake();

        $run = app(ScrapeRunner::class)->ingestHtml(
            $this->source(),
            'https://example.test/p/1',
            $this->page(),
        );

        $item = ScrapeItem::first();

        $this->assertSame(ScrapeRun::STATUS_COMPLETED, $run->status);
        $this->assertSame(1, $run->items_new);
        $this->assertNotNull($item);
        $this->assertSame(ScrapeItem::STATUS_PENDING, $item->status);
        $this->assertSame('Kristýna', $item->normalized['display_name']);
        $this->assertSame('Brno', $item->normalized['city']);
        $this->assertSame(['https://example.test/1.jpg'], $item->images);

        Http::assertNothingSent();
    }

    /** Adresa rozhoduje o tom, jestli profil už máme. */
    public function test_pasting_the_same_profile_twice_updates_it(): void
    {
        Http::fake();

        $source = $this->source();
        $runner = app(ScrapeRunner::class);

        $runner->ingestHtml($source, 'https://example.test/p/1', $this->page());
        $second = $runner->ingestHtml($source->fresh(), 'https://example.test/p/1', $this->page(city: 'Praha'));

        $this->assertSame(1, ScrapeItem::count());
        $this->assertSame(1, $second->items_updated);
        $this->assertSame('Praha', ScrapeItem::first()->normalized['city']);
    }

    /** Věková pojistka platí i tady. Obejít se nedá ničím. */
    public function test_the_age_guard_still_applies(): void
    {
        Http::fake();

        app(ScrapeRunner::class)->ingestHtml(
            $this->source(),
            'https://example.test/p/1',
            $this->page(age: '17'),
        );

        $item = ScrapeItem::first();

        $this->assertSame(ScrapeItem::STATUS_REJECTED, $item->status);
        $this->assertStringContainsString('pod hranicí', (string) $item->error);
    }

    /** Vlastní pravidla zdroje taky. */
    public function test_the_source_rules_still_apply(): void
    {
        Http::fake();

        app(ScrapeRunner::class)->ingestHtml(
            $this->source(['content_rules' => 'city != Brno']),
            'https://example.test/p/1',
            $this->page(city: 'Ostrava'),
        );

        $this->assertSame(ScrapeItem::STATUS_REJECTED, ScrapeItem::first()->status);
    }

    /** Chybějící povinné pole se pozná stejně jako při stahování. */
    public function test_a_page_without_the_required_field_fails(): void
    {
        Http::fake();

        app(ScrapeRunner::class)->ingestHtml(
            $this->source(),
            'https://example.test/p/1',
            '<html><body><div class="mesto">Brno</div></body></html>',
        );

        $item = ScrapeItem::first();

        $this->assertSame(ScrapeItem::STATUS_FAILED, $item->status);
        $this->assertStringContainsString('display_name', (string) $item->error);
    }

    /** Stránka uložená z prohlížeče může být v jiném kódování. */
    public function test_a_windows_1250_page_is_converted(): void
    {
        Http::fake();

        $html = (string) iconv('UTF-8', 'Windows-1250', $this->page());

        app(ScrapeRunner::class)->ingestHtml($this->source(), 'https://example.test/p/1', $html);

        $this->assertSame('Kristýna', ScrapeItem::first()->normalized['display_name']);
    }

    /** Běh je dohledatelný jako každý jiný. */
    public function test_the_run_records_where_it_came_from(): void
    {
        Http::fake();

        $run = app(ScrapeRunner::class)->ingestHtml(
            $this->source(),
            'https://example.test/p/1',
            $this->page(),
        );

        // Adresy jsou to jediné, podle čeho se zpětně pozná, co v dávce bylo.
        $this->assertSame(['https://example.test/p/1'], $run->options['ingest']);
        $this->assertStringContainsString('vložená stránka', (string) $run->log);
    }
}
