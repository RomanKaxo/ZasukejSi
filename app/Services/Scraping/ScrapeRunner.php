<?php

namespace App\Services\Scraping;

use App\Models\ScrapeItem;
use App\Models\ScrapeRun;
use App\Models\ScrapeSource;
use Closure;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Walks a source: listing pages, detail pages, field extraction, staging.
 *
 * Items land as `pending`. Nothing here touches `profiles` — importing is a
 * separate, deliberate step so scraped data is always reviewed first.
 */
class ScrapeRunner
{
    public function __construct(
        private readonly HttpFetcher $fetcher,
        private readonly FieldExtractor $extractor,
        private readonly AdapterRegistry $adapters,
        private readonly DuplicateFinder $duplicates,
        private readonly UnknownValueCollector $unknownValues,
        private readonly SitemapReader $sitemap,
    ) {
    }

    /**
     * @param  array{limit?: int, pages?: int, url?: string, dry_run?: bool}  $options
     * @param  Closure(string, array): void|null  $progress
     */
    public function run(ScrapeSource $source, array $options = [], ?Closure $progress = null): ScrapeRun
    {
        $run = ScrapeRun::create([
            'scrape_source_id' => $source->id,
            'status' => ScrapeRun::STATUS_RUNNING,
            'started_at' => now(),
            'options' => $options,
            // Set explicitly rather than relying on the column defaults, so the
            // counters read as 0 on the returned model even when nothing was
            // incremented during the run.
            'pages_fetched' => 0,
            'items_found' => 0,
            'items_new' => 0,
            'items_updated' => 0,
            'items_failed' => 0,
        ]);

        // The narration is kept on the run as well as handed to the caller.
        // It used to exist only for the console command, so a run started from
        // the admin could report counts but never show what it saw.
        $log = [];

        $notify = function (string $message, array $data = []) use ($progress, &$log): void {
            $line = $message;

            if ($data !== []) {
                $line .= ' — ' . self::describe($data);
            }

            // Bounded so a large harvest cannot turn one row into megabytes.
            if (count($log) < self::LOG_LINE_LIMIT) {
                $log[] = $line;
            } elseif (count($log) === self::LOG_LINE_LIMIT) {
                $log[] = '… další řádky se už nezaznamenávají.';
            }

            if ($progress !== null) {
                $progress($message, $data);
            }
        };

        // The same guard the console command applies, enforced here so the
        // admin actions cannot bypass it either: a disabled source may be
        // examined but not harvested.
        if (! $source->is_enabled && ! ($options['dry_run'] ?? false)) {
            $run->forceFill([
                'status' => ScrapeRun::STATUS_FAILED,
                'error' => 'Zdroj je vypnutý. Zapněte ho, nebo použijte zkušební běh.',
                'finished_at' => now(),
            ])->save();

            $notify('Zdroj je vypnutý — nic se nestahovalo.');

            return $run;
        }

        try {
            $this->rememberRobots($source, $notify);

            $urls = isset($options['url'])
                ? [$options['url']]
                : array_values(array_unique(array_merge(
                    // Failures first: a page that timed out is the one thing
                    // we already know is missing, and a change-aware run will
                    // not offer it again on its own.
                    $this->retryUrls($source, $options, $notify),
                    $this->collectDetailUrls($source, $run, $options, $notify),
                )));

            $limit = (int) ($options['limit'] ?? 0);

            if ($limit > 0) {
                $urls = array_slice($urls, 0, $limit);
            }

            $run->items_found = count($urls);
            $notify('Nalezeno profilů: ' . count($urls));

            foreach ($urls as $url) {
                $this->processDetail($source, $run, $url, (bool) ($options['dry_run'] ?? false), $notify);
            }

            $run->status = ScrapeRun::STATUS_COMPLETED;
        } catch (Throwable $e) {
            $run->status = ScrapeRun::STATUS_FAILED;
            $run->error = $e->getMessage();

            Log::error('Scrape run failed', [
                'source' => $source->slug,
                'run' => $run->id,
                'exception' => $e,
            ]);

            $notify('CHYBA: ' . $e->getMessage());
        }

        // A trial run is somebody trying something out; it must not pause a
        // working source, and it must not clear the record of a broken one.
        if (! ($options['dry_run'] ?? false)) {
            $this->recordHealth($source, $run, $notify);
        }

        $run->finished_at = now();
        $run->log = $log === [] ? null : implode("\n", $log);
        $run->save();

        return $run;
    }

    /**
     * Addresses of pages that failed and are due for another attempt.
     *
     * @param  array{limit?: int, pages?: int, url?: string, dry_run?: bool}  $options
     * @return array<int, string>
     */
    private function retryUrls(ScrapeSource $source, array $options, Closure $notify): array
    {
        // A trial run is somebody checking one thing; it must not quietly drag
        // the whole backlog along and spend the limit on it.
        if ($options['dry_run'] ?? false) {
            return [];
        }

        $items = ScrapeItem::query()
            ->dueForRetry($source->id)
            ->orderBy('retry_after')
            ->limit(self::RETRY_BATCH)
            ->get();

        if ($items->isEmpty()) {
            return [];
        }

        $notify('Znovu se zkusí dřív neúspěšných adres: ' . $items->count());

        return $items->pluck('source_url')->all();
    }

    /**
     * Record that a detail page could not be read, and when to try again.
     *
     * A failure used to leave nothing behind but a number in the counter, so
     * the address was lost until somebody walked the whole listing again.
     */
    private function recordFailedDetail(ScrapeSource $source, ScrapeRun $run, string $url, string $externalId, string $error, Closure $notify): void
    {
        $item = ScrapeItem::firstOrNew([
            'scrape_source_id' => $source->id,
            'external_id' => $externalId,
        ]);

        $attempts = (int) $item->attempts + 1;
        $maxAttempts = max(1, (int) $source->setting('max_attempts', 5));

        $retryAt = $attempts >= $maxAttempts ? null : ScrapeItem::nextRetryAt($attempts);

        $item->fill([
            'scrape_run_id' => $run->id,
            'source_url' => $url,
            'attempts' => $attempts,
            'last_attempt_at' => now(),
            'retry_after' => $retryAt,
            'error' => $error,
        ]);

        // An item somebody has already dealt with keeps its verdict: a page
        // that stops responding must not undo an import or a rejection.
        if (! in_array($item->status, [ScrapeItem::STATUS_IMPORTED, ScrapeItem::STATUS_REJECTED, ScrapeItem::STATUS_APPROVED], true)) {
            $item->status = ScrapeItem::STATUS_FAILED;
        }

        $item->save();

        $notify($retryAt === null
            ? "Detail {$url} selhal už {$attempts}×, automaticky se zkoušet nebude. Zbývá ruční pokus."
            : "Detail {$url} selhal ({$attempts}. pokus), znovu " . $retryAt->format('j. n. H:i') . '.');
    }

    /**
     * Keep the source's health up to date, and take it out of rotation when
     * it has failed often enough that asking again is doing harm.
     */
    private function recordHealth(ScrapeSource $source, ScrapeRun $run, Closure $notify): void
    {
        if ($run->status === ScrapeRun::STATUS_COMPLETED) {
            $wasPaused = $source->isPaused();
            $source->recordSuccess();

            if ($wasPaused) {
                $notify('Zdroj zase funguje, pauza zrušena.');
            }

            return;
        }

        if ($source->recordFailure((string) $run->error)) {
            $notify(sprintf(
                'Zdroj pozastaven po %d neúspěších v řadě. Až chybu vyřešíte, spusťte běh ručně — tím se pauza zruší.',
                $source->consecutive_failures,
            ));
        }
    }

    /**
     * Marks the item if it looks like somebody we already have.
     *
     * Never fatal: a harvest must not fail because the comparison did. The
     * reviewer sees an unchecked item, which is the state everything was in
     * before this existed.
     */
    private function flagDuplicate(ScrapeItem $item, Closure $notify): void
    {
        try {
            $this->duplicates->check($item);
        } catch (Throwable $e) {
            $notify('kontrola duplicity selhala: ' . $e->getMessage());

            return;
        }

        if ($item->hasDuplicate()) {
            $notify('možný duplikát: ' . $item->duplicateLabel());
        }
    }

    /**
     * Puts values our catalogue does not know into the review queue.
     *
     * Never fatal for the same reason the duplicate check is not: a harvest
     * must not fail because a comparison did.
     */
    private function collectUnknownValues(ScrapeItem $item, Closure $notify): void
    {
        try {
            $noted = $this->unknownValues->collect($item);
        } catch (Throwable $e) {
            $notify('sběr neznámých hodnot selhal: ' . $e->getMessage());

            return;
        }

        if ($noted > 0) {
            $notify("neznámých hodnot k doplnění: {$noted}");
        }
    }

    /** Keeps one row from growing without bound on a large harvest. */
    private const LOG_LINE_LIMIT = 500;

    /**
     * How many failed pages one run picks up.
     *
     * Bounded so a source that broke for a week does not turn the next
     * scheduled run into a thousand-page catch-up against a site that may
     * still be unwell.
     */
    private const RETRY_BATCH = 50;

    /**
     * The extra data a notification carries, flattened to one readable line.
     *
     * A dry run reports the values it extracted this way, which is the whole
     * point of running one.
     */
    private static function describe(array $data): string
    {
        $parts = [];

        foreach ($data as $key => $value) {
            if ($key === 'values' && is_array($value)) {
                foreach ($value as $field => $fieldValue) {
                    $parts[] = $field . '=' . self::stringify($fieldValue);
                }

                continue;
            }

            $parts[] = $key . '=' . self::stringify($value);
        }

        return implode(', ', $parts);
    }

    private static function stringify(mixed $value): string
    {
        if (is_array($value)) {
            return '[' . implode('|', array_map(
                fn ($item) => is_scalar($item) ? (string) $item : gettype($item),
                $value
            )) . ']';
        }

        if (is_bool($value)) {
            return $value ? 'ano' : 'ne';
        }

        return (string) ($value ?? '—');
    }

    /**
     * robots.txt is read once per run and stored, so the admin can see what the
     * site asked for and the effective delay is auditable.
     */
    private function rememberRobots(ScrapeSource $source, Closure $notify): void
    {
        if (! $source->setting('respect_robots', true)) {
            $notify('robots.txt se ignoruje (vypnuto v nastavení zdroje)');

            return;
        }

        $robots = $this->fetcher->robotsFor($source);

        $source->forceFill([
            'robots_rules' => $robots->toArray(),
            'robots_checked_at' => now(),
        ])->save();

        $notify(sprintf(
            'robots.txt: %d zákazů, crawl-delay %s → použije se %.1f s',
            count($robots->disallow),
            $robots->crawlDelay === null ? 'neuveden' : $robots->crawlDelay . ' s',
            $source->effectiveCrawlDelay(),
        ));
    }

    /** @return array<int, string> */
    private function collectDetailUrls(ScrapeSource $source, ScrapeRun $run, array $options, Closure $notify): array
    {
        // A sitemap is the site itself listing every address it wants found,
        // so there is nothing left to guess — no link selector, no page
        // numbering, no wondering where the listing ends. Where a source has
        // one, walking the listing is the slower way to learn less.
        if ($source->setting('discovery') === 'sitemap') {
            return $this->collectFromSitemap($source, $run, $options, $notify);
        }

        $adapter = $this->adapters->make($source->adapter);
        $maxPages = (int) ($options['pages'] ?? $source->setting('max_pages'));
        $followLinks = $source->setting('pagination_mode') === 'next_link';
        $urls = [];
        $listingUrl = null;

        for ($page = 1; $page <= max(1, $maxPages); $page++) {
            // Numbered paging computes the address. Link-following reads it
            // off the previous page and only needs a starting point, which is
            // what sites with infinite scroll or opaque cursors give us.
            $listingUrl = ($followLinks && $listingUrl !== null && $page > 1)
                ? $listingUrl
                : $adapter->listingUrl($source, $page);

            try {
                $html = $this->fetcher->get($source, $listingUrl);
            } catch (Throwable $e) {
                $notify("Výpis {$listingUrl} selhal: " . $e->getMessage());

                // Nothing was reachable at all. That is a failed run, not a
                // source with nothing on it — reporting success here is how a
                // site that had started refusing us went on looking healthy
                // for weeks while it harvested nothing.
                if ($page === 1) {
                    throw $e;
                }

                // A listing page that errors is not the same as one that is
                // empty, but the run used to end on both and report success.
                // A wrong pagination setting then looked like "the source only
                // has one page" — which is how a harvest of Brno stopped at 23
                // profiles out of a hundred.
                if ($page > 1 && $run->error === null) {
                    $run->error = "Stránkování se zastavilo na {$listingUrl}: " . $e->getMessage()
                        . ' Zkontrolujte pagination_pattern a pagination_param.';
                }

                break;
            }

            $run->increment('pages_fetched');

            $found = $adapter->detailUrls($source, $html);
            $fresh = array_diff($found, $urls);

            $notify("Stránka {$page}: " . count($found) . ' odkazů, z toho nových ' . count($fresh));

            // An empty page means the end of the listing, not an error.
            if ($found === []) {
                break;
            }

            // A page that repeats what we already have is the end too. Some
            // listings ignore the pagination parameter entirely and keep
            // serving page one — eurogirlsescort.cz does exactly that for a
            // city with a single page, and we were politely re-fetching the
            // same twenty-three profiles twelve times over.
            if ($page > 1 && $fresh === []) {
                $notify("Stránka {$page} nepřinesla nic nového — konec výpisu.");
                break;
            }

            $urls = array_merge($urls, $fresh);

            if ($followLinks) {
                $next = $adapter->nextListingUrl($source, $html, $listingUrl);

                if ($next === null) {
                    $notify('Web už další stránku nenabízí, končím výpis.');

                    break;
                }

                $listingUrl = $next;
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * Detail addresses read out of the site's sitemap.
     *
     * `lastmod` is what turns a nightly run from „stáhni celý web znovu" into
     * „stáhni ty tři profily, které se hnuly".
     *
     * @param  array{limit?: int, pages?: int, url?: string, dry_run?: bool}  $options
     * @return array<int, string>
     */
    private function collectFromSitemap(ScrapeSource $source, ScrapeRun $run, array $options, Closure $notify): array
    {
        $since = null;

        if ($source->setting('sitemap_changed_only', true) && $source->last_success_at) {
            $since = $source->last_success_at;
            $notify('Ze sitemapy beru jen to, co se změnilo od ' . $since->format('j. n. Y H:i'));
        }

        $urls = $this->sitemap->detailUrls($source, $since);

        $run->increment('pages_fetched');
        $notify('Sitemapa nabídla adres: ' . count($urls));

        if ($urls === [] && $since !== null) {
            $notify('Od posledního úspěšného běhu se nezměnilo nic.');
        }

        return $urls;
    }

    private function processDetail(ScrapeSource $source, ScrapeRun $run, string $url, bool $dryRun, Closure $notify): void
    {
        $adapter = $this->adapters->make($source->adapter);
        $externalId = $adapter->externalId($source, $url);

        $existing = $dryRun
            ? null
            : ScrapeItem::where('scrape_source_id', $source->id)
                ->where('external_id', $externalId)
                ->first();

        try {
            // Ask conditionally only where we already hold a copy. A page we
            // have never seen has to arrive in full, otherwise a 304 against a
            // stale cache row would silently lose the profile.
            $html = $existing
                ? $this->fetcher->getIfChanged($source, $url)
                : $this->fetcher->get($source, $url);
        } catch (Throwable $e) {
            $run->increment('items_failed');

            if ($dryRun) {
                $notify("Detail {$url} selhal: " . $e->getMessage());
            } else {
                $this->recordFailedDetail($source, $run, $url, $externalId, $e->getMessage(), $notify);
            }

            return;
        }

        // The site itself said nothing moved: no parsing, no writes, and above
        // all no re-downloading photographs we already have on disk.
        if ($html === null) {
            $existing->update(['scrape_run_id' => $run->id]);
            $notify("beze změny (hlásí web): {$url}");

            return;
        }

        $result = $this->extractor->extract($html, $source->fieldMaps, $source);
        $images = $adapter->imageUrls($source, $html);

        $normalized = $result['values'];
        $hash = hash('sha256', json_encode([$normalized, $images], JSON_THROW_ON_ERROR));

        if ($dryRun) {
            $notify('DRY-RUN ' . $url, ['values' => $normalized, 'images' => count($images), 'missing' => $result['missing']]);

            return;
        }

        // Unchanged pages still get their run stamped so it is visible the
        // profile was seen, but they are not counted as updated.
        if ($existing && $existing->content_hash === $hash) {
            $existing->update(['scrape_run_id' => $run->id]);
            $notify("beze změny: {$url}");

            return;
        }

        $attributes = [
            'scrape_source_id' => $source->id,
            'scrape_run_id' => $run->id,
            'source_url' => $url,
            'external_id' => $externalId,
            'content_hash' => $hash,
            'raw' => ['missing' => $result['missing']],
            'normalized' => $normalized,
            'images' => $images,
            'error' => $result['missing'] === [] ? null : 'Chybí povinná pole: ' . implode(', ', $result['missing']),
            // The page answered, so whatever went wrong before is over.
            'attempts' => 0,
            'last_attempt_at' => now(),
            'retry_after' => null,
        ];

        if ($existing) {
            // A rejected item stays rejected; a re-scrape must not quietly put
            // it back in the review queue.
            if (! in_array($existing->status, [ScrapeItem::STATUS_REJECTED, ScrapeItem::STATUS_IMPORTED], true)) {
                $attributes['status'] = $result['missing'] === []
                    ? ScrapeItem::STATUS_PENDING
                    : ScrapeItem::STATUS_FAILED;
            }

            $existing->update($attributes);
            $this->flagDuplicate($existing, $notify);
            $this->collectUnknownValues($existing, $notify);
            $run->increment('items_updated');
            $notify("aktualizováno: {$url}");

            return;
        }

        $attributes['status'] = $result['missing'] === []
            ? ScrapeItem::STATUS_PENDING
            : ScrapeItem::STATUS_FAILED;

        $item = ScrapeItem::create($attributes);
        $this->flagDuplicate($item, $notify);
        $this->collectUnknownValues($item, $notify);
        $run->increment('items_new');
        $notify("nové: {$url}");
    }
}
