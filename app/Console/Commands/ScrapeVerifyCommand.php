<?php

namespace App\Console\Commands;

use App\Models\ScrapeSource;
use App\Services\Scraping\ProfileExistenceChecker;
use Illuminate\Console\Command;
use Throwable;

/**
 * Asks every source whether the profiles we took from it are still there.
 *
 * Scraping only ever adds. Without this, a woman who stopped advertising in
 * March is still on our site in December, public and looking current.
 *
 * Nothing is deleted or hidden here. The command marks and reports; the
 * decision is made by a person in the admin.
 */
class ScrapeVerifyCommand extends Command
{
    protected $signature = 'scrape:verify
        {--source= : Limit to one source slug}
        {--limit= : How many profiles to check per source}';

    protected $description = 'Check that imported profiles still exist at their source';

    public function handle(ProfileExistenceChecker $checker): int
    {
        $query = ScrapeSource::query()->where('is_enabled', true);

        if ($slug = $this->option('source')) {
            $query->where('slug', $slug);
        }

        $sources = $query->get();

        if ($sources->isEmpty()) {
            $this->info('Není co kontrolovat.');

            return self::SUCCESS;
        }

        $failed = 0;

        foreach ($sources as $source) {
            // A source that is refusing us would report every profile as
            // unreadable; that is not evidence and the check would only make
            // the block worse.
            if ($source->isPaused()) {
                $this->line("Zdroj {$source->slug} je pozastavený, přeskakuji.");

                continue;
            }

            $this->info("Zdroj: {$source->name} ({$source->slug})");

            try {
                $result = $checker->check(
                    $source,
                    $this->option('limit') ? (int) $this->option('limit') : null,
                    fn (string $line) => $this->line('  ' . $line),
                );
            } catch (Throwable $e) {
                $this->error('  ' . $e->getMessage());
                $failed++;

                continue;
            }

            $this->line(sprintf(
                '  zkontrolováno %d, zmizelo %d, vrátilo se %d',
                $result['checked'],
                $result['missing'],
                $result['recovered'],
            ));

            if ($result['missing'] > 0) {
                $this->warn('  Čekají na rozhodnutí v administraci. Nic se nesmazalo.');
            }
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
