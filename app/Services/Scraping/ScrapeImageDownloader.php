<?php

namespace App\Services\Scraping;

use App\Models\Profile;
use App\Models\ScrapeItem;
use Illuminate\Support\Str;
use Throwable;

/**
 * Downloads a scraped item's photos into the profile's media collection.
 *
 * Requests go through HttpFetcher so the source's crawl delay applies to the
 * media host too, and every file keeps the URL it came from in its custom
 * properties — provenance has to survive the import.
 */
class ScrapeImageDownloader
{
    public function __construct(private readonly HttpFetcher $fetcher)
    {
    }

    /** @return int Number of images stored. */
    public function download(ScrapeItem $item, Profile $profile): int
    {
        $urls = array_values(array_filter((array) $item->images));

        if ($urls === []) {
            return 0;
        }

        $source = $item->source;
        $limit = (int) $source->setting('image_limit', 10);

        if ($limit > 0) {
            $urls = array_slice($urls, 0, $limit);
        }

        $stored = 0;
        $failures = [];

        foreach ($urls as $index => $url) {
            try {
                $binary = $this->fetcher->getBinary($source, $url);
            } catch (Throwable $e) {
                $failures[] = $url;

                continue;
            }

            $temporary = tempnam(sys_get_temp_dir(), 'scrape_');

            if ($temporary === false) {
                $failures[] = $url;

                continue;
            }

            try {
                file_put_contents($temporary, $binary);

                $profile
                    ->addMedia($temporary)
                    ->usingFileName($this->fileName($url, $index))
                    ->withCustomProperties([
                        'scraped_from' => $url,
                        'scrape_item_id' => $item->id,
                        'scrape_source' => $source->slug,
                    ])
                    ->toMediaCollection('profile-images');

                $stored++;
            } catch (Throwable $e) {
                $failures[] = $url;
            } finally {
                if (is_file($temporary)) {
                    @unlink($temporary);
                }
            }
        }

        if ($failures !== []) {
            $item->forceFill([
                'error' => count($failures) . ' z ' . count($urls) . ' fotek se nepodařilo stáhnout.',
            ])->save();
        }

        return $stored;
    }

    private function fileName(string $url, int $index): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $extension = Str::lower(pathinfo($path, PATHINFO_EXTENSION));

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }

        return sprintf('scraped-%02d.%s', $index + 1, $extension);
    }
}
