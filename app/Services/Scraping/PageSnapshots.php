<?php

namespace App\Services\Scraping;

use App\Models\ScrapeItem;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Keeps the last page each item was read from.
 *
 * Fixing a selector meant fetching the page again — once to see what went
 * wrong, once per attempt after that. Every one of those is a request to
 * somebody else's server for a page we had already downloaded and thrown away,
 * and on a site that has started refusing us it is not even possible.
 *
 * The copy is what the extractor actually saw: already converted to UTF-8,
 * already the body the selectors ran against. Trying a selector on it is
 * therefore the same experiment as trying it live, minus the request.
 *
 * Kept on disk rather than in a column: a detail page is a few hundred
 * kilobytes and the item table is read on every admin screen. Gzipped, because
 * HTML compresses to about a tenth and this is bulk nobody reads directly.
 */
class PageSnapshots
{
    private const DISK = 'local';

    private const DIRECTORY = 'scrape-snapshots';

    /** Store the page an item was read from. Never fatal. */
    public function put(ScrapeItem $item, string $html): void
    {
        if (! $item->source?->setting('keep_snapshot', true)) {
            return;
        }

        try {
            Storage::disk(self::DISK)->put($this->path($item), (string) gzencode($html, 6));
        } catch (Throwable) {
            // A snapshot is a convenience. Losing it must not cost the harvest.
        }
    }

    /** The stored page, or null when there is none. */
    public function get(ScrapeItem $item): ?string
    {
        try {
            $path = $this->path($item);

            if (! Storage::disk(self::DISK)->exists($path)) {
                return null;
            }

            $raw = Storage::disk(self::DISK)->get($path);

            if ($raw === null || $raw === '') {
                return null;
            }

            $html = @gzdecode($raw);

            return is_string($html) && $html !== '' ? $html : null;
        } catch (Throwable) {
            return null;
        }
    }

    public function has(ScrapeItem $item): bool
    {
        try {
            return Storage::disk(self::DISK)->exists($this->path($item));
        } catch (Throwable) {
            return false;
        }
    }

    public function forget(ScrapeItem $item): void
    {
        try {
            Storage::disk(self::DISK)->delete($this->path($item));
        } catch (Throwable) {
            //
        }
    }

    /**
     * Drop snapshots of items nobody has touched for a while.
     *
     * @return int How many files went.
     */
    public function prune(int $days): int
    {
        $cutoff = now()->subDays($days);
        $removed = 0;

        ScrapeItem::query()
            ->where('updated_at', '<', $cutoff)
            ->select(['id', 'scrape_source_id'])
            ->chunkById(500, function ($items) use (&$removed) {
                foreach ($items as $item) {
                    if ($this->has($item)) {
                        $this->forget($item);
                        $removed++;
                    }
                }
            });

        return $removed;
    }

    private function path(ScrapeItem $item): string
    {
        // Rozdělené po zdrojích, ať se v adresáři dá vyznat, a ať smazání
        // jednoho webu neznamená procházet všechno ostatní.
        return sprintf('%s/%d/%d.html.gz', self::DIRECTORY, $item->scrape_source_id, $item->id);
    }
}
