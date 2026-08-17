<?php

namespace App\Console\Commands;

use App\Models\ScrapeItem;
use App\Models\ScrapeUnknownValue;
use App\Services\Scraping\UnknownValueCollector;
use Illuminate\Console\Command;

/**
 * Fills the "to be added" queue from items that are already staged.
 *
 * Runs are what normally feed the queue, but the harvest that revealed the
 * problem happened before it existed — this catches those up without
 * re-fetching anything from the source.
 */
class ScrapeUnknownValuesCommand extends Command
{
    protected $signature = 'scrape:unknown-values
        {--approve-all : Add every value found straight into the catalogue}
        {--resync : Re-apply what we scraped to profiles already imported}
        {--list : Only print what is missing, change nothing}';

    protected $description = 'Collect values the catalogue does not know from staged items';

    public function handle(UnknownValueCollector $collector): int
    {
        $items = ScrapeItem::query()->orderBy('id')->get();

        if ($items->isEmpty()) {
            $this->info('Žádné položky.');

            return self::SUCCESS;
        }

        if ($this->option('list')) {
            return $this->listOnly($items, $collector);
        }

        // Profiles imported before a field had anywhere to go keep whatever the
        // importer could store at the time; this hands them the rest.
        if ($this->option('resync')) {
            return $this->resyncOnly($items);
        }

        $noted = 0;

        foreach ($items as $item) {
            $noted += $collector->collect($item);
        }

        $pending = ScrapeUnknownValue::query()->pending()->count();

        $this->info("Zaznamenáno výskytů: {$noted}");
        $this->info("Různých hodnot čeká na doplnění: {$pending}");

        if ($this->option('approve-all')) {
            $added = 0;

            foreach (ScrapeUnknownValue::query()->pending()->get() as $value) {
                if ($value->approve()) {
                    $added++;
                }
            }

            $collector->forget();
            $this->info("Doplněno do katalogu: {$added}");

            $this->releaseBlocked($collector);
        }

        $this->newLine();
        $this->line('Fronta je v administraci pod Scraper → K doplnění.');

        return self::SUCCESS;
    }

    /**
     * Let go of what the newly added values were holding up.
     *
     * Items still waiting get approved; profiles imported while the value was
     * missing get the rest of their services, because those names were dropped
     * at import time and nothing went back for them.
     */
    private function releaseBlocked(UnknownValueCollector $collector): void
    {
        $importer = app(\App\Services\Scraping\ScrapeItemImporter::class);
        $approved = 0;

        foreach ($collector->unblockedItems() as $item) {
            if ($item->wasBlockedByUnknownValue() && $item->status === ScrapeItem::STATUS_PENDING) {
                $item->update(['status' => ScrapeItem::STATUS_APPROVED]);
                $approved++;
            }
        }

        $resynced = 0;

        foreach (ScrapeItem::query()->where('status', ScrapeItem::STATUS_IMPORTED)->withUnknownValues()->get() as $item) {
            $importer->resync($item);
            $resynced++;
        }

        $this->info("Automaticky schváleno položek: {$approved}");
        $this->info("Doplněny hodnoty u již vytvořených profilů: {$resynced}");
    }

    /**
     * Hand already-imported profiles what the importer could not store before.
     *
     * @param  \Illuminate\Support\Collection<int, ScrapeItem>  $items
     */
    private function resyncOnly($items): int
    {
        $importer = app(\App\Services\Scraping\ScrapeItemImporter::class);
        $touched = 0;

        foreach ($items->where('status', ScrapeItem::STATUS_IMPORTED) as $item) {
            if ($item->profile) {
                $importer->resync($item);
                $touched++;
            }
        }

        $this->info("Doplněno profilů: {$touched}");

        return self::SUCCESS;
    }

    /** @param  \Illuminate\Support\Collection<int, ScrapeItem>  $items */
    private function listOnly($items, UnknownValueCollector $collector): int
    {
        $labels = \App\Models\ScrapeUnknownValue::fieldOptions();
        $counts = [];

        foreach ($items as $item) {
            foreach ($collector->unknownValues($item) as $field => $values) {
                foreach ($values as $value) {
                    $key = $field . '|' . ScrapeUnknownValue::normalize($value);
                    $counts[$key] ??= ['field' => $field, 'value' => $value, 'count' => 0];
                    $counts[$key]['count']++;
                }
            }
        }

        if ($counts === []) {
            $this->info('Katalog zná všechno, co položky zmiňují.');

            return self::SUCCESS;
        }

        // Grouped by field, commonest first inside each — the order an admin
        // works through the queue in.
        usort($counts, fn ($a, $b) => [$a['field'], -$a['count']] <=> [$b['field'], -$b['count']]);

        $this->table(
            ['pole', 'hodnota', 'výskytů'],
            array_map(fn ($row) => [
                $labels[$row['field']] ?? $row['field'],
                $row['value'],
                $row['count'],
            ], $counts),
        );

        return self::SUCCESS;
    }
}
