<?php

namespace App\Console\Commands;

use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Services\Scraping\DuplicateFinder;
use App\Services\Scraping\ScrapeItemImporter;
use Illuminate\Console\Command;
use Throwable;

/**
 * Turns staged items into profiles in bulk.
 *
 * Importing was a per-row action in the admin, so a harvest of two dozen
 * profiles meant two dozen confirmations — and photo downloads wait out the
 * source's crawl delay, which is not something to sit through in a browser
 * tab.
 *
 * What it creates is still unpublished and pending: this moves rows into the
 * approval queue, it does not put anything on the site.
 */
class ScrapeImportCommand extends Command
{
    protected $signature = 'scrape:import
        {source? : Slug of the source, otherwise every source}
        {--approve : Approve pending items first instead of only importing approved ones}
        {--limit= : Stop after this many items}
        {--without-images : Skip the photo download}
        {--skip-duplicates : Leave items flagged as a possible duplicate alone}';

    protected $description = 'Create profiles from approved scrape items';

    public function handle(ScrapeItemImporter $importer, DuplicateFinder $duplicates): int
    {
        $query = ScrapeItem::query();

        if ($slug = $this->argument('source')) {
            $source = ScrapeSource::where('slug', $slug)->first();

            if (! $source) {
                $this->error("Zdroj [{$slug}] neexistuje.");

                return self::FAILURE;
            }

            $query->where('scrape_source_id', $source->id);
        }

        $statuses = $this->option('approve')
            ? [ScrapeItem::STATUS_PENDING, ScrapeItem::STATUS_APPROVED]
            : [ScrapeItem::STATUS_APPROVED];

        $query->whereIn('status', $statuses);

        if ($this->option('skip-duplicates')) {
            $query->whereNull('duplicate_profile_id')->whereNull('duplicate_item_id');
        }

        if ($limit = (int) $this->option('limit')) {
            $query->limit($limit);
        }

        $items = $query->orderBy('id')->get();

        if ($items->isEmpty()) {
            $this->info('Není co importovat.');

            return self::SUCCESS;
        }

        $withImages = ! $this->option('without-images');

        $this->info("Položek k importu: {$items->count()}" . ($withImages ? ' (včetně fotografií)' : ' (bez fotografií)'));

        if ($withImages) {
            $this->line('Fotografie se stahují s prodlevou podle nastavení zdroje, takže to potrvá.');
        }

        $created = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $name = $item->value('display_name') ?: ('#' . $item->id);

            // The verdict is a snapshot from when the item was staged; a
            // profile created by this very run would not be in it.
            $duplicates->check($item);

            if ($this->option('skip-duplicates') && $item->hasDuplicate()) {
                $this->line("  přeskočeno (duplicita): {$name} — " . $item->duplicateLabel());
                $skipped++;

                continue;
            }

            if ($item->status === ScrapeItem::STATUS_PENDING) {
                $item->update(['status' => ScrapeItem::STATUS_APPROVED]);
            }

            try {
                $profile = $importer->import($item, $withImages);
                $photos = $profile->getAllImages()->count();

                $this->line("  profil #{$profile->id}: {$name} — fotografií {$photos}"
                    . ($item->hasDuplicate() ? '  [možná duplicita: ' . $item->duplicateLabel() . ']' : ''));
                $created++;
            } catch (Throwable $e) {
                // One bad row must not stop the rest; the reason stays on it.
                $item->forceFill([
                    'status' => ScrapeItem::STATUS_FAILED,
                    'error' => $e->getMessage(),
                ])->save();

                $this->error("  selhalo: {$name} — " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->table(
            ['vytvořeno', 'selhalo', 'přeskočeno'],
            [[$created, $failed, $skipped]],
        );
        $this->line('Profily jsou nepublikované a čekají na schválení.');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
