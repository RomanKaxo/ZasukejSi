<?php

namespace App\Services\Scraping;

use App\Models\Profile;
use App\Models\ScrapeItem;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * Downloads a scraped item's photos into the profile's media collection.
 *
 * Requests go through HttpFetcher so the source's crawl delay applies to the
 * media host too, and every file keeps the URL it came from in its custom
 * properties — provenance has to survive the import.
 *
 * Nothing is stored twice. A re-scrape used to add the whole gallery again,
 * so a profile that had been re-imported three times carried three copies of
 * every photograph: disk paid for it, and the visitor scrolled through the
 * same woman nine times.
 *
 * Two guards, because they catch different things. The address is checked
 * first and costs no request at all. The content hash catches what the
 * address cannot: the same photograph served from a new URL after a redesign,
 * or twice under different names in the same gallery.
 */
class ScrapeImageDownloader
{
    public function __construct(private readonly HttpFetcher $fetcher)
    {
    }

    /** @return int Number of images stored. */
    public function download(ScrapeItem $item, Profile $profile): int
    {
        // The same address twice in one gallery is common enough — a thumbnail
        // and its full size often resolve to one file.
        $urls = array_values(array_unique(array_filter((array) $item->images)));

        if ($urls === []) {
            return 0;
        }

        $source = $item->source;
        $limit = (int) $source->setting('image_limit', 10);

        if ($limit > 0) {
            $urls = array_slice($urls, 0, $limit);
        }

        $known = $this->existing($profile);

        $stored = 0;
        $skipped = 0;
        $failures = [];

        foreach ($urls as $index => $url) {
            // Already have this exact address: no request, no file, no row.
            if (in_array($url, $known['urls'], true)) {
                $skipped++;

                continue;
            }

            try {
                $binary = $this->fetcher->getBinary($source, $url);
            } catch (Throwable) {
                $failures[] = $url;

                continue;
            }

            $hash = sha1($binary);

            // Same picture, different address. Worth the download to find out:
            // the alternative is a duplicate nobody can spot afterwards.
            if (in_array($hash, $known['hashes'], true)) {
                $skipped++;

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
                        // What makes the next import able to tell this is the
                        // same photograph even from a different address.
                        'content_sha1' => $hash,
                    ])
                    ->toMediaCollection('profile-images');

                $known['urls'][] = $url;
                $known['hashes'][] = $hash;
                $stored++;
            } catch (Throwable) {
                $failures[] = $url;
            } finally {
                if (is_file($temporary)) {
                    @unlink($temporary);
                }
            }
        }

        $this->report($item, count($urls), $failures, $skipped);

        return $stored;
    }

    /**
     * The addresses and content hashes this profile already holds.
     *
     * Photographs added by hand have no hash — they were never downloaded by
     * us — so they are hashed on first sight and the answer kept, which means
     * an operator's upload is not duplicated by a later scrape either.
     *
     * @return array{urls: array<int, string>, hashes: array<int, string>}
     */
    private function existing(Profile $profile): array
    {
        $urls = [];
        $hashes = [];

        foreach ($profile->getMedia('profile-images') as $media) {
            $url = $media->getCustomProperty('scraped_from');

            if (is_string($url) && $url !== '') {
                $urls[] = $url;
            }

            $hash = $media->getCustomProperty('content_sha1');

            if (is_string($hash) && $hash !== '') {
                $hashes[] = $hash;

                continue;
            }

            $hash = $this->hashOf($media);

            if ($hash !== null) {
                $hashes[] = $hash;
            }
        }

        return ['urls' => $urls, 'hashes' => $hashes];
    }

    /** Hash a file already on disk, and remember it for next time. */
    private function hashOf(Media $media): ?string
    {
        try {
            $path = $media->getPath();

            if (! is_file($path)) {
                return null;
            }

            $hash = sha1_file($path);

            if ($hash === false) {
                return null;
            }

            $media->setCustomProperty('content_sha1', $hash);
            $media->save();

            return $hash;
        } catch (Throwable) {
            // A missing file must not stop an import; it only means this one
            // cannot take part in the comparison.
            return null;
        }
    }

    /** @param array<int, string> $failures */
    private function report(ScrapeItem $item, int $total, array $failures, int $skipped): void
    {
        $notes = [];

        if ($failures !== []) {
            $notes[] = count($failures) . ' z ' . $total . ' fotek se nepodařilo stáhnout.';
        }

        if ($skipped > 0) {
            $notes[] = $skipped . ' fotek už profil měl, znovu se nestahovaly.';
        }

        if ($notes === []) {
            return;
        }

        $item->forceFill(['error' => implode(' ', $notes)])->save();
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
