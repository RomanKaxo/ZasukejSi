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
            $importer->resyncServices($item);
            $resynced++;
        }

        $this->info("Automaticky schváleno položek: {$approved}");
        $this->info("Doplněny služby u již vytvořených profilů: {$resynced}");
    }

    /** @param  \Illuminate\Support\Collection<int, ScrapeItem>  $items */
    private function listOnly($items, UnknownValueCollector $collector): int
    {
        $counts = [];

        foreach ($items as $item) {
            foreach ($collector->unknownServices($item) as $name) {
                $key = ScrapeUnknownValue::normalize($name);
                $counts[$key] ??= ['value' => $name, 'count' => 0];
                $counts[$key]['count']++;
            }
        }

        if ($counts === []) {
            $this->info('Katalog zná všechno, co položky zmiňují.');

            return self::SUCCESS;
        }

        usort($counts, fn ($a, $b) => $b['count'] <=> $a['count']);

        $this->table(
            ['hodnota', 'výskytů'],
            array_map(fn ($row) => [$row['value'], $row['count']], $counts),
        );

        return self::SUCCESS;
    }
}
