<?php

namespace App\Console\Commands;

use App\Models\ScrapeSource;
use App\Services\Scraping\ScrapeRunner;
use Illuminate\Console\Command;

class ScrapeRunCommand extends Command
{
    protected $signature = 'scrape:run
        {source : Slug of the source}
        {--pages= : How many listing pages to walk}
        {--limit= : Stop after this many detail pages}
        {--url= : Scrape one detail URL instead of walking the listing}
        {--dry-run : Fetch and extract, write nothing}';

    protected $description = 'Scrape a configured source into the review queue';

    public function handle(ScrapeRunner $runner): int
    {
        $source = ScrapeSource::where('slug', $this->argument('source'))->first();

        if (! $source) {
            $this->error("Zdroj [{$this->argument('source')}] neexistuje.");

            return self::FAILURE;
        }

        if (! $source->is_enabled && ! $this->option('dry-run')) {
            $this->error("Zdroj [{$source->slug}] je vypnutý. Zapněte ho v administraci, nebo použijte --dry-run.");

            return self::FAILURE;
        }

        if ($source->fieldMaps()->count() === 0) {
            $this->warn('Zdroj nemá nastavené mapování polí — stáhnou se jen odkazy a fotky.');
        }

        $options = array_filter([
            'pages' => $this->option('pages') !== null ? (int) $this->option('pages') : null,
            'limit' => $this->option('limit') !== null ? (int) $this->option('limit') : null,
            'url' => $this->option('url'),
            'dry_run' => $this->option('dry-run') ?: null,
        ], fn ($value) => $value !== null);

        $this->info("Zdroj: {$source->name} ({$source->base_url})");

        $run = $runner->run($source, $options, function (string $message, array $data = []) {
            $this->line('  ' . $message);

            if ($data !== []) {
                $this->line('    ' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }
        });

        $this->newLine();
        $this->table(
            ['běh', 'stav', 'stránky', 'nalezeno', 'nové', 'změněné', 'chyby'],
            [[
                $run->id,
                $run->status,
                $run->pages_fetched,
                $run->items_found,
                $run->items_new,
                $run->items_updated,
                $run->items_failed,
            ]],
        );

        if ($run->error) {
            $this->error($run->error);

            return self::FAILURE;
        }

        if (! $this->option('dry-run')) {
            $this->info('Položky čekají ke kontrole v administraci. Nic se nepublikovalo.');
        }

        return self::SUCCESS;
    }
}
