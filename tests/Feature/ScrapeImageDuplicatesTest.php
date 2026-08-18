<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Models\User;
use App\Services\Scraping\ScrapeImageDownloader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Fotky se nesmí ukládat dvakrát.
 *
 * Opakovaný scrape přidával celou galerii znovu, takže profil po třetím
 * importu nesl tři kopie každé fotky — disk to platil a návštěvník scrolloval
 * tutéž dívku devětkrát.
 *
 * Dvě pojistky, protože každá chytá něco jiného: adresa nestojí ani jeden
 * požadavek, otisk obsahu chytne tutéž fotku na nové adrese po redesignu.
 */
class ScrapeImageDuplicatesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Testovací obrázky se skládají GD-čkem; bez něj není co stahovat.
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('ext-gd není k dispozici.');
        }

        Storage::fake('public');
    }

    /** Malý platný obrázek, ať knihovna médií má co uložit. */
    private function pixel(string $colour = 'cerveny'): string
    {
        $image = imagecreatetruecolor(2, 2);

        imagefill($image, 0, 0, match ($colour) {
            'cerveny' => imagecolorallocate($image, 220, 20, 20),
            default => imagecolorallocate($image, 20, 20, 220),
        });

        ob_start();
        imagejpeg($image, null, 90);
        $binary = (string) ob_get_clean();
        imagedestroy($image);

        return $binary;
    }

    private function source(): ScrapeSource
    {
        return ScrapeSource::create([
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => ['crawl_delay' => 0, 'respect_robots' => false],
        ]);
    }

    private function profile(): Profile
    {
        return Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
        ]);
    }

    private function item(ScrapeSource $source, array $images): ScrapeItem
    {
        return ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/p/1',
            'external_id' => '1',
            'status' => ScrapeItem::STATUS_APPROVED,
            'images' => $images,
        ]);
    }

    public function test_the_same_gallery_imported_twice_stores_each_photo_once(): void
    {
        Http::fake([
            'https://example.test/1.jpg' => Http::response($this->pixel('cerveny'), 200),
            'https://example.test/2.jpg' => Http::response($this->pixel('modry'), 200),
        ]);

        $source = $this->source();
        $profile = $this->profile();
        $item = $this->item($source, ['https://example.test/1.jpg', 'https://example.test/2.jpg']);

        $downloader = app(ScrapeImageDownloader::class);

        $this->assertSame(2, $downloader->download($item, $profile));
        $this->assertSame(0, $downloader->download($item->fresh(), $profile->fresh()));

        $this->assertSame(2, $profile->fresh()->getMedia('profile-images')->count());
    }

    /** Tatáž fotka na nové adrese — to pozná až otisk obsahu. */
    public function test_the_same_photo_at_a_new_address_is_not_stored_again(): void
    {
        Http::fake([
            'https://example.test/stara/1.jpg' => Http::response($this->pixel('cerveny'), 200),
            'https://example.test/nova/1.jpg' => Http::response($this->pixel('cerveny'), 200),
        ]);

        $source = $this->source();
        $profile = $this->profile();

        $downloader = app(ScrapeImageDownloader::class);

        $downloader->download($this->item($source, ['https://example.test/stara/1.jpg']), $profile);

        $second = ScrapeItem::create([
            'scrape_source_id' => $source->id,
            'source_url' => 'https://example.test/p/2',
            'external_id' => '2',
            'status' => ScrapeItem::STATUS_APPROVED,
            'images' => ['https://example.test/nova/1.jpg'],
        ]);

        $this->assertSame(0, $downloader->download($second, $profile->fresh()));
        $this->assertSame(1, $profile->fresh()->getMedia('profile-images')->count());
    }

    /** Náhled a plná velikost vedou na tutéž adresu — jednou stačí. */
    public function test_a_repeated_address_in_one_gallery_is_downloaded_once(): void
    {
        Http::fake([
            'https://example.test/1.jpg' => Http::response($this->pixel('cerveny'), 200),
        ]);

        $profile = $this->profile();
        $item = $this->item($this->source(), [
            'https://example.test/1.jpg',
            'https://example.test/1.jpg',
        ]);

        $this->assertSame(1, app(ScrapeImageDownloader::class)->download($item, $profile));
    }

    /** Uložené fotky si nesou otisk, aby ho příští import nemusel dopočítávat. */
    public function test_a_stored_photo_carries_its_fingerprint(): void
    {
        $binary = $this->pixel('cerveny');

        Http::fake(['https://example.test/1.jpg' => Http::response($binary, 200)]);

        $profile = $this->profile();

        app(ScrapeImageDownloader::class)->download(
            $this->item($this->source(), ['https://example.test/1.jpg']),
            $profile,
        );

        $media = $profile->fresh()->getMedia('profile-images')->first();

        $this->assertSame(sha1($binary), $media->getCustomProperty('content_sha1'));
        $this->assertSame('https://example.test/1.jpg', $media->getCustomProperty('scraped_from'));
    }

    /** Přeskočené fotky se mají zmínit, ať to nevypadá, že se import nepovedl. */
    public function test_skipped_photos_are_reported_on_the_item(): void
    {
        Http::fake(['https://example.test/1.jpg' => Http::response($this->pixel('cerveny'), 200)]);

        $source = $this->source();
        $profile = $this->profile();
        $item = $this->item($source, ['https://example.test/1.jpg']);

        $downloader = app(ScrapeImageDownloader::class);
        $downloader->download($item, $profile);
        $downloader->download($item->fresh(), $profile->fresh());

        $this->assertStringContainsString('už profil měl', (string) $item->fresh()->error);
    }
}
