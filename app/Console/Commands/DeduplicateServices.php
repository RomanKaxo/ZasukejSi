<?php

namespace App\Console\Commands;

use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Removes duplicate services left behind by the old seeder.
 *
 * `services.name` is a translatable JSON column, and the seeder matched on the
 * whole encoded value — which never equalled the stored row, so every db:seed
 * appended the list again. The seeder is fixed; this clears what it produced.
 *
 * Profile links are moved to the surviving row before a duplicate is deleted,
 * so no profile loses a service.
 */
class DeduplicateServices extends Command
{
    protected $signature = 'services:deduplicate {--dry-run : Report what would happen and change nothing}';

    protected $description = 'Merge duplicate services, keeping the oldest of each name';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $groups = Service::all()->groupBy(
            fn (Service $service) => Str::lower(trim((string) $service->getTranslation('name', 'cs')))
        );

        $duplicated = $groups->filter(fn ($rows) => $rows->count() > 1);

        if ($duplicated->isEmpty()) {
            $this->info('Žádné duplicity. Služeb celkem: ' . Service::count());

            return self::SUCCESS;
        }

        $this->table(
            ['služba', 'výskytů'],
            $duplicated->map(fn ($rows, $name) => [$name, $rows->count()])->values()->all(),
        );

        if ($dryRun) {
            $this->warn('Zkušební běh — nic se nezměnilo.');

            return self::SUCCESS;
        }

        $removed = 0;
        $remapped = 0;

        foreach ($duplicated as $rows) {
            $keep = $rows->sortBy('id')->first();

            foreach ($rows->where('id', '!=', $keep->id) as $duplicate) {
                $remapped += DB::table('profile_service')
                    ->where('service_id', $duplicate->id)
                    ->update(['service_id' => $keep->id]);

                $duplicate->delete();
                $removed++;
            }
        }

        // Remapping can leave a profile pointing at the kept service twice.
        $this->dropDuplicatePivots();

        $this->info("Smazáno {$removed} duplicit, přepojeno {$remapped} vazeb. Zbývá služeb: " . Service::count());

        return self::SUCCESS;
    }

    private function dropDuplicatePivots(): void
    {
        $duplicates = DB::table('profile_service')
            ->select('profile_id', 'service_id')
            ->groupBy('profile_id', 'service_id')
            ->havingRaw('count(*) > 1')
            ->get();

        foreach ($duplicates as $pair) {
            $ids = DB::table('profile_service')
                ->where('profile_id', $pair->profile_id)
                ->where('service_id', $pair->service_id)
                ->orderBy('id')
                ->pluck('id')
                ->slice(1);

            DB::table('profile_service')->whereIn('id', $ids)->delete();
        }
    }
}
