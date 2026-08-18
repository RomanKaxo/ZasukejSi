<?php

namespace App\Console\Commands;

use App\Models\ScrapeRun;
use App\Models\ScrapeUrlCache;
use App\Services\Scraping\PageSnapshots;
use Illuminate\Console\Command;

/**
 * Throws away scraper bookkeeping nobody will read again.
 *
 * Two tables grow forever and neither is anybody's data. The URL cache holds
 * one row per address ever fetched, kept only so the next run can ask „changed
 * since?" — an address the site has dropped will never be asked about again,
 * and its row sits there for good. Runs keep a log of up to five hundred lines
 * each; useful for a week, ballast after a month.
 *
 * Deliberately touches neither `scrape_items` nor profiles. Those are the
 * harvest, and deleting a harvest is a decision, not maintenance.
 */
class ScrapePruneCommand extends Command
{
    protected $signature = 'scrape:prune
        {--cache-days=60 : Drop cached addresses not seen for this many days}
        {--run-days=90 : Drop finished runs older than this}
        {--snapshot-days=30 : Drop stored pages of items untouched for this long}
        {--dry-run : Report what would go, delete nothing}';

    protected $description = 'Prune the scraper URL cache and old run history';

    public function handle(PageSnapshots $snapshots): int
    {
        $cacheDays = max(1, (int) $this->option('cache-days'));
        $runDays = max(1, (int) $this->option('run-days'));
        $dryRun = (bool) $this->option('dry-run');

        $staleCache = ScrapeUrlCache::query()->where('fetched_at', '<', now()->subDays($cacheDays));

        // Runs still going are left alone whatever their age: a harvest that
        // waits out a crawl delay between requests can legitimately take days.
        $oldRuns = ScrapeRun::query()
            ->whereNotNull('finished_at')
            ->where('finished_at', '<', now()->subDays($runDays));

        if ($dryRun) {
            $this->line(sprintf(
                'Smazalo by se: %d adres z mezipaměti, %d běhů.',
                $staleCache->count(),
                $oldRuns->count(),
            ));

            return self::SUCCESS;
        }

        $deletedSnapshots = $snapshots->prune(max(1, (int) $this->option('snapshot-days')));
        $deletedCache = $this->deleteInChunks($staleCache);
        $deletedRuns = $this->deleteInChunks($oldRuns);

        $this->info("Smazáno: {$deletedCache} adres z mezipaměti, {$deletedRuns} běhů, {$deletedSnapshots} uložených stránek.");

        return self::SUCCESS;
    }

    /**
     * Delete by id in batches.
     *
     * `DELETE ... LIMIT` is not portable — SQLite only has it when compiled
     * for it — and a year of scraping is too many rows for one statement.
     */
    private function deleteInChunks(\Illuminate\Database\Eloquent\Builder $query): int
    {
        $deleted = 0;

        while (true) {
            $ids = (clone $query)->limit(1000)->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $deleted += $query->getModel()->newQuery()->whereIn('id', $ids)->delete();
        }

        return $deleted;
    }
}
