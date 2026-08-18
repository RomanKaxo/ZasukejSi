<?php

namespace App\Console\Commands;

use App\Models\ScrapeSource;
use App\Services\Scraping\ScrapeRunner;
use Illuminate\Console\Command;
use Throwable;

/**
 * Runs the sources whose slot has come.
 *
 * Harvesting used to require somebody to click a button or type a command, so
 * anything recurring depended on remembering to do it. Sources without an
 * interval are untouched, which is every source until one is given.
 */
class ScrapeDueCommand extends Command
{
    protected $signature = 'scrape:due
        {--source= : Limit to one source slug}
        {--force : Ignore the schedule and run the source now}';

    protected $description = 'Run every scrape source whose scheduled slot has come';

    public function handle(ScrapeRunner $runner): int
    {
        $query = $this->option('force')
            ? ScrapeSource::query()->where('is_enabled', true)
            : ScrapeSource::query()->due();

        if ($slug = $this->option('source')) {
            $query->where('slug', $slug);
        }

        $sources = $query->get();

        // The window is hours and weekdays, which no database expresses
        // usefully, so it is applied here rather than in the scope. `--force`
        // is a person asking on purpose and is not held to it.
        if (! $this->option('force')) {
            $waiting = $sources->reject(fn (ScrapeSource $source) => $source->isWithinWindow());

            foreach ($waiting as $source) {
                $opens = $source->windowOpensAt();

                $this->line(sprintf(
                    'Zdroj %s čeká na své okno%s.',
                    $source->slug,
                    $opens ? ' — otevře se ' . $opens->format('j. n. H:i') : '',
                ));
            }

            $sources = $sources->diff($waiting);
        }

        if ($sources->isEmpty()) {
            $this->info('Žádný zdroj není na řadě.');

            return self::SUCCESS;
        }

        $failed = 0;

        foreach ($sources as $source) {
            $this->info("Zdroj: {$source->name} ({$source->slug})");

            try {
                $run = $runner->run($source, array_filter([
                    'pages' => $source->schedule_pages,
                    'limit' => $source->schedule_limit,
                ]));

                $this->line(sprintf(
                    '  %s — nalezeno %d, nových %d, změněných %d, chyb %d',
                    $run->status,
                    $run->items_found,
                    $run->items_new,
                    $run->items_updated,
                    $run->items_failed,
                ));

                if ($run->error) {
                    $this->warn('  ' . $run->error);
                    $failed++;
                }
            } catch (Throwable $e) {
                // One broken source must not stop the others, and it must not
                // stay due forever either — the slot moves regardless.
                $this->error('  ' . $e->getMessage());
                $failed++;
            }

            // The runner may have taken the source out of rotation. Said here
            // as well as stored, because cron mails the output and that is
            // where somebody will actually see it.
            if ($source->fresh()?->isPaused()) {
                $this->error('  ZDROJ POZASTAVEN — ' . ($source->fresh()->paused_reason ?: 'bez uvedeného důvodu'));
                $this->line('  Automatické běhy se zastavily. Zrušte pauzu v administraci, až bude chyba vyřešená.');
            }

            $source->refresh()->scheduleNextRun();

            $this->line('  další běh: ' . ($source->next_run_at?->format('d.m.Y H:i') ?? '—'));
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
