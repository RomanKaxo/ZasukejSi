<?php

namespace App\Services\Scraping;

use App\Models\Notification;
use App\Models\ScrapeItem;
use App\Models\ScrapeRun;
use App\Models\ScrapeSource;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
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
        private readonly RevisionRecorder $revisions,
        private readonly PageSnapshots $snapshots,
        private readonly AgeGuard $ageGuard,
        private readonly ContentRules $rules,
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

        // Cron a kliknutí v administraci se snadno potkají a dva běhy téhož
        // zdroje zdvojnásobí zátěž cizího webu — přesně to, čemu se prodleva
        // mezi dotazy snaží zabránit, protože prodleva se počítá zvlášť v
        // každém procesu.
        $lock = Cache::lock('scrape:source:' . $source->id, self::LOCK_SECONDS);

        if (! $lock->get()) {
            $run->forceFill([
                'status' => ScrapeRun::STATUS_FAILED,
                'error' => 'Tenhle zdroj právě běží. Počkejte, až doběhne — dva běhy naráz by web zatížily dvakrát.',
                'finished_at' => now(),
            ])->save();

            $notify('Zdroj právě běží, tenhle běh se nespouští.');

            return $run;
        }

        try {
            $this->rememberRobots($source, $notify);

            $this->requests = 0;

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

            $this->emptyExtractions = 0;
            $this->detailsProcessed = 0;

            $maxRequests = (int) $source->setting('max_requests', 0);

            foreach ($urls as $url) {
                // Strop na celý běh. Chybné stránkování dokáže vyrobit
                // nekonečný seznam adres a bez tohohle by scraper poslušně
                // ťukal na cizí server, dokud ho někdo nezastaví.
                if ($maxRequests > 0 && $this->requests >= $maxRequests) {
                    $notify("Dosažen strop {$maxRequests} požadavků na běh, zbytek se nechává na příště.");

                    if ($run->error === null) {
                        $run->error = "Běh se zastavil na stropu {$maxRequests} požadavků. Zbývající adresy se stáhnou při dalším běhu.";
                    }

                    break;
                }

                $this->processDetail($source, $run, $url, (bool) ($options['dry_run'] ?? false), $notify);
                $this->guardAgainstRedesign($source, $notify);
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
            $this->announce($source, $run, $options);
        }

        $lock->release();

        $run->finished_at = now();
        $run->log = $log === [] ? null : implode("\n", $log);
        $run->save();

        return $run;
    }

    /**
     * Whether writing this extraction would throw away everything we had.
     *
     * @param  array<string, mixed>  $values
     * @param  array<int, string>  $missing
     */
    private function wouldEraseEverything(ScrapeItem $existing, array $values, array $missing): bool
    {
        // Nothing to lose, or nothing required to lose it by.
        if ($missing === [] || ! is_array($existing->normalized) || $existing->normalized === []) {
            return false;
        }

        // The new read produced nothing usable at all.
        return array_filter($values, fn ($value) => $value !== null && $value !== '' && $value !== []) === [];
    }

    /**
     * Stop a run that has started reading nothing.
     *
     * When a site is redesigned every selector breaks at once, and the run
     * that follows is not a harvest — it is a hundred profiles being marked
     * broken, one after another, by a scraper that cannot read the page any
     * more. The first few are enough to know; the remaining ninety-five are
     * pointless requests to somebody else's server.
     *
     * Deliberately a share rather than a count: a site with a handful of
     * genuinely incomplete profiles is normal, a site where four in five come
     * back empty is not.
     */
    private function guardAgainstRedesign(ScrapeSource $source, Closure $notify): void
    {
        if (! $source->setting('redesign_guard', true)) {
            return;
        }

        $minimum = max(3, (int) $source->setting('redesign_min_items', 5));

        if ($this->detailsProcessed < $minimum) {
            return;
        }

        $ratio = $this->emptyExtractions / max(1, $this->detailsProcessed);
        $threshold = (float) $source->setting('redesign_ratio', 0.8);

        if ($ratio < $threshold) {
            return;
        }

        $notify(sprintf(
            'Z %d stránek jich %d nevrátilo povinná pole. Běh se zastavuje.',
            $this->detailsProcessed,
            $this->emptyExtractions,
        ));

        throw new RuntimeException(sprintf(
            'Vypadá to, že se web předělal: %d z %d stránek nevrátilo povinná pole. '
            . 'Běh se zastavil, aby nepřepsal zbytek profilů prázdnem. '
            . 'Zkontrolujte selektory v Dílně — data zůstala, jak byla.',
            $this->emptyExtractions,
            $this->detailsProcessed,
        ));
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
     * One line in the admin about how the run went.
     *
     * The narration lives on the run and nobody reads it: a night's harvest
     * ends in a row on a screen somebody would have to think to open. What an
     * operator wants the next morning is one sentence — and only when there is
     * something in it.
     *
     * Deliberately quiet about the ordinary case. A scheduled run that found
     * nothing new is the system working, and a notification every night for
     * that is how notifications stop being read.
     *
     * @param  array{limit?: int, pages?: int, url?: string, dry_run?: bool}  $options
     */
    private function announce(ScrapeSource $source, ScrapeRun $run, array $options): void
    {
        // Ruční běh: obsluha u toho seděla a výsledek už viděla.
        if (! $source->isScheduled() && ! ($options['scheduled'] ?? false)) {
            return;
        }

        if ($run->status === ScrapeRun::STATUS_FAILED) {
            Notification::forAdmins(
                'Scraper selhal: ' . $source->name,
                ($run->error ?: 'Bez bližšího důvodu.')
                    . ' Podrobnosti jsou v běhu #' . $run->id . '.',
                'error',
            );

            return;
        }

        $worthSaying = $run->items_new > 0 || $run->items_failed > 0;

        if (! $worthSaying) {
            return;
        }

        Notification::forAdmins(
            'Scraper: ' . $source->name,
            sprintf(
                'Nalezeno %d, nových %d, změněných %d, chyb %d.%s',
                $run->items_found,
                $run->items_new,
                $run->items_updated,
                $run->items_failed,
                $run->items_new > 0 ? ' Nové čekají ve frontě ke kontrole.' : '',
            ),
            $run->items_failed > 0 ? 'warning' : 'info',
        );
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
            $message = sprintf(
                'Zdroj pozastaven po %d neúspěších v řadě. Až chybu vyřešíte, spusťte běh ručně — tím se pauza zruší.',
                $source->consecutive_failures,
            );

            $notify($message);

            Notification::forAdmins(
                'Scraper pozastavil zdroj: ' . $source->name,
                $message . ' Poslední chyba: ' . ($run->error ?: 'neuvedena'),
                'error',
            );
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
     * How long the source stays locked.
     *
     * Long enough for a real harvest — a crawl delay of five seconds over five
     * hundred profiles is the best part of an hour — and short enough that a
     * process killed mid-run does not lock the source out for a day.
     */
    private const LOCK_SECONDS = 7200;

    /** Kolik detailů v tomhle běhu nevrátilo povinná pole. */
    private int $emptyExtractions = 0;

    private int $detailsProcessed = 0;

    /** Kolik stránek si tenhle běh vyžádal — kvůli stropu. */
    private int $requests = 0;

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
                $this->requests++;
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

        // Rozhodnutí, které se nedělá v revizi. Reviewer proklikávající
        // padesát profilů v jedenáct večer je přesně ten mechanismus, který
        // tady nesmí být poslední obranou.
        $minimumAge = $this->ageGuard->minimumFor((int) $source->setting('minimum_age', AgeGuard::MINIMUM));

        $existing = $dryRun
            ? null
            : ScrapeItem::where('scrape_source_id', $source->id)
                ->where('external_id', $externalId)
                ->first();

        try {
            // Ask conditionally only where we already hold a copy. A page we
            // have never seen has to arrive in full, otherwise a 304 against a
            // stale cache row would silently lose the profile.
            $this->requests++;

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

        $this->storeDetail($source, $run, $url, $html, $dryRun, $notify);
    }

    /**
     * Take a page somebody else downloaded and put it through the mill.
     *
     * The one route that needs no request at all. A site can refuse this
     * server while a person sitting at an ordinary browser reaches it without
     * trouble; this lets them save that page and hand it in. The selectors,
     * the age guard, the rules, the duplicate check and the review queue are
     * exactly the same — the only difference is where the HTML came from.
     *
     * @param  Closure(string, array): void|null  $progress
     */
    public function ingestHtml(ScrapeSource $source, string $url, string $html, ?Closure $progress = null): ScrapeRun
    {
        return $this->ingestMany($source, [['url' => $url, 'html' => $html]], $progress);
    }

    /**
     * Several pages at once, as one run.
     *
     * A ZIP of fifty saved pages is one act of harvesting, not fifty — and
     * fifty runs in the history would bury everything else that happened that
     * day. One page that cannot be read must not stop the other forty-nine
     * either: it is recorded and the batch carries on.
     *
     * @param  iterable<int, array{url: string, html: string}>  $pages
     * @param  Closure(string, array): void|null  $progress
     */
    public function ingestMany(ScrapeSource $source, iterable $pages, ?Closure $progress = null): ScrapeRun
    {
        $log = [];

        $notify = function (string $message, array $data = []) use ($progress, &$log): void {
            if (count($log) < self::LOG_LINE_LIMIT) {
                $log[] = $data === [] ? $message : $message . ' — ' . self::describe($data);
            }

            if ($progress !== null) {
                $progress($message, $data);
            }
        };

        $run = ScrapeRun::create([
            'scrape_source_id' => $source->id,
            'status' => ScrapeRun::STATUS_RUNNING,
            'started_at' => now(),
            // Doplní se po zpracování: adresy jsou to jediné, podle čeho se
            // zpětně pozná, co v té dávce vlastně bylo.
            'options' => ['ingest' => []],
            'pages_fetched' => 0,
            'items_found' => 0,
            'items_new' => 0,
            'items_updated' => 0,
            'items_failed' => 0,
        ]);

        $encoding = new PageEncoding();
        $found = 0;
        $addresses = [];

        foreach ($pages as $page) {
            $url = trim((string) ($page['url'] ?? ''));
            $html = (string) ($page['html'] ?? '');

            if ($url === '' || $html === '') {
                $run->increment('items_failed');
                $notify('Přeskočeno: chybí adresa nebo obsah stránky.');

                continue;
            }

            $found++;

            if (count($addresses) < 50) {
                $addresses[] = $url;
            }

            $notify('Zpracovává se vložená stránka: ' . $url);

            try {
                // Vložené HTML může být uložené z prohlížeče v jiném kódování,
                // stejně jako by přišlo po drátě.
                $this->storeDetail($source, $run, $url, $encoding->toUtf8($html), false, $notify);
            } catch (Throwable $e) {
                // Jedna rozbitá stránka nesmí shodit celou dávku.
                $run->increment('items_failed');
                $notify('CHYBA u ' . $url . ': ' . $e->getMessage());
            }
        }

        $run->items_found = $found;
        $run->options = ['ingest' => $addresses];
        $run->status = ScrapeRun::STATUS_COMPLETED;
        $run->finished_at = now();
        $run->log = $log === [] ? null : implode("\n", $log);
        $run->save();

        return $run;
    }

    /**
     * Turn a page into a queued item.
     *
     * Split out from the fetching on purpose: the HTML does not have to come
     * from our own request. Where a site refuses this server outright, a
     * person with ordinary access to it can save the page and hand it in —
     * same selectors, same guards, same review queue, no fetch at all.
     */
    private function storeDetail(ScrapeSource $source, ScrapeRun $run, string $url, string $html, bool $dryRun, Closure $notify): void
    {
        $adapter = $this->adapters->make($source->adapter);
        $externalId = $adapter->externalId($source, $url);
        $minimumAge = $this->ageGuard->minimumFor((int) $source->setting('minimum_age', AgeGuard::MINIMUM));

        $existing = $dryRun
            ? null
            : ScrapeItem::where('scrape_source_id', $source->id)
                ->where('external_id', $externalId)
                ->first();

        $result = $this->extractor->extract($html, $source->fieldMaps, $source);
        $images = $adapter->imageUrls($source, $html);

        $this->detailsProcessed++;

        if ($result['missing'] !== []) {
            $this->emptyExtractions++;
        }

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

        $blocked = $this->ageGuard->isBlocked($normalized, $minimumAge);
        $blockReason = $blocked ? $this->ageGuard->reason($normalized, $minimumAge) : null;

        // Pravidla zdroje. Věková pojistka je v kódu, protože se o ní
        // nediskutuje; tohle je všechno ostatní, co se u konkrétního webu
        // ukáže jako potřeba a co má měnit ten, kdo si toho všimne.
        if (! $blocked) {
            $violated = $this->rules->violation($source, $normalized);

            if ($violated !== null) {
                $blocked = true;
                $blockReason = 'Odmítnuto pravidlem zdroje: ' . $violated;
            }
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
            // A page that suddenly yields nothing is a broken selector far
            // more often than a woman who deleted her whole advert and left
            // the page up. Overwriting good values with emptiness is the one
            // outcome that cannot be undone, so it does not happen: the item
            // keeps what it had and the failure is recorded instead.
            if ($this->wouldEraseEverything($existing, $normalized, $result['missing'])) {
                $run->increment('items_failed');
                $this->recordFailedDetail(
                    $source,
                    $run,
                    $url,
                    $externalId,
                    'Stránka nevrátila žádné z povinných polí, přestože dřív vracela. Data zůstala beze změny.',
                    $notify,
                );

                return;
            }

            // Recorded before the row is overwritten — afterwards the previous
            // values are gone and „aktualizováno" is the whole story anybody
            // ever gets.
            $revision = $this->revisions->record($existing, $run, $normalized, $images);

            // A rejected item stays rejected; a re-scrape must not quietly put
            // it back in the review queue.
            if ($blocked) {
                // Nikdy se nedostane do fronty ke schválení, ani kdyby tam
                // předtím byla.
                $attributes['status'] = ScrapeItem::STATUS_REJECTED;
                $attributes['error'] = $blockReason;
                $notify('ZABLOKOVÁNO: ' . $url . ' — ' . $blockReason);
            } elseif (! in_array($existing->status, [ScrapeItem::STATUS_REJECTED, ScrapeItem::STATUS_IMPORTED], true)) {
                $attributes['status'] = $result['missing'] === []
                    ? ScrapeItem::STATUS_PENDING
                    : ScrapeItem::STATUS_FAILED;
            }

            $existing->update($attributes);
            // Uloženo až po zápisu: dokud položka nemá výsledná data, nemá
            // smysl si k nim schovávat stránku.
            $this->snapshots->put($existing, $html);
            $this->flagDuplicate($existing, $notify);
            $this->collectUnknownValues($existing, $notify);
            $run->increment('items_updated');
            $notify("aktualizováno: {$url}" . ($revision ? ' — ' . $revision->summary() : ''));

            return;
        }

        if ($blocked) {
            $attributes['status'] = ScrapeItem::STATUS_REJECTED;
            $attributes['error'] = $blockReason;
            $notify('ZABLOKOVÁNO: ' . $url . ' — ' . $blockReason);
        } else {
            $attributes['status'] = $result['missing'] === []
                ? ScrapeItem::STATUS_PENDING
                : ScrapeItem::STATUS_FAILED;
        }

        $item = ScrapeItem::create($attributes);
        $this->snapshots->put($item, $html);
        $this->flagDuplicate($item, $notify);
        $this->collectUnknownValues($item, $notify);
        $run->increment('items_new');
        $notify("nové: {$url}");
    }
}
