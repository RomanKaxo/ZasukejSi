<?php

namespace App\Console\Commands;

use App\Models\Translation;
use App\Support\Locales;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

/**
 * Load the strings from lang/ into the `translations` table so they show up in
 * the admin.
 *
 * Files stay authoritative for defaults. This only makes them visible and
 * editable: a row's `default_value` always tracks the file, while `value` is
 * whatever the operator has set. An edited value is never overwritten unless
 * --force is passed.
 */
class ImportTranslations extends Command
{
    protected $signature = 'translations:import
                            {--locale=* : Limit to these locales (defaults to every configured locale)}
                            {--group=* : Limit to these lang files, e.g. --group=front}
                            {--force : Overwrite values edited in the admin with the file value}
                            {--prune : Delete rows whose key no longer exists in the files}';

    protected $description = 'Import lang/ files into the editable translations table';

    public function handle(): int
    {
        $locales = $this->option('locale') ?: Locales::codes();
        $groupFilter = $this->option('group');
        $force = (bool) $this->option('force');

        $imported = 0;
        $updated = 0;
        $seen = [];

        foreach ($locales as $locale) {
            $dir = lang_path($locale);

            if (! is_dir($dir)) {
                $this->warn("Skipping {$locale}: lang/{$locale} does not exist.");
                continue;
            }

            foreach (File::files($dir) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $group = $file->getFilenameWithoutExtension();

                if ($groupFilter !== [] && ! in_array($group, $groupFilter, true)) {
                    continue;
                }

                $lines = require $file->getPathname();

                if (! is_array($lines)) {
                    continue;
                }

                foreach (Arr::dot($lines) as $key => $value) {
                    // Only leaf strings are editable; nested arrays are
                    // flattened by Arr::dot, and anything non-scalar (a
                    // closure, an object) is not something an admin can type.
                    if (! is_string($value) && ! is_numeric($value)) {
                        continue;
                    }

                    $seen[] = $locale . '|' . $group . '|' . $key;

                    [$wasCreated, $wasUpdated] = $this->upsert($locale, $group, $key, (string) $value, $force);
                    $imported += $wasCreated ? 1 : 0;
                    $updated += $wasUpdated ? 1 : 0;
                }
            }

            // JSON translations: the ones keyed by the sentence itself rather
            // than by a dotted path.
            $jsonPath = lang_path($locale . '.json');

            if (File::exists($jsonPath) && ($groupFilter === [] || in_array(Translation::JSON_GROUP, $groupFilter, true))) {
                $json = json_decode(File::get($jsonPath), true) ?: [];

                foreach ($json as $key => $value) {
                    if (! is_string($value)) {
                        continue;
                    }

                    $seen[] = $locale . '|' . Translation::JSON_GROUP . '|' . $key;

                    [$wasCreated, $wasUpdated] = $this->upsert($locale, Translation::JSON_GROUP, $key, $value, $force);
                    $imported += $wasCreated ? 1 : 0;
                    $updated += $wasUpdated ? 1 : 0;
                }
            }
        }

        $pruned = $this->option('prune') ? $this->prune($locales, $seen) : 0;

        Translation::flushCache();

        $this->info("Imported {$imported} new, refreshed {$updated} defaults" . ($pruned ? ", pruned {$pruned}" : '') . '.');

        return self::SUCCESS;
    }

    /**
     * @return array{0: bool, 1: bool} [created, defaultRefreshed]
     */
    private function upsert(string $locale, string $group, string $key, string $fileValue, bool $force): array
    {
        // Keys longer than the column would silently truncate and then collide.
        if (mb_strlen($key) > 191) {
            $this->warn("Skipping over-long key in {$group} ({$locale}): " . mb_substr($key, 0, 60) . '…');

            return [false, false];
        }

        $existing = Translation::query()
            ->where(compact('locale', 'group', 'key'))
            ->first();

        if (! $existing) {
            Translation::create([
                'locale' => $locale,
                'group' => $group,
                'key' => $key,
                'value' => $fileValue,
                'default_value' => $fileValue,
            ]);

            return [true, false];
        }

        $refreshed = false;

        // The file is the default, so it always wins for `default_value`.
        if ($existing->default_value !== $fileValue) {
            $existing->default_value = $fileValue;
            $refreshed = true;
        }

        // An admin edit is only discarded on --force.
        if ($force || ! $existing->isOverridden()) {
            $existing->value = $fileValue;
        }

        if ($existing->isDirty()) {
            $existing->save();
        }

        return [false, $refreshed];
    }

    /**
     * @param  array<int, string>  $locales
     * @param  array<int, string>  $seen
     */
    private function prune(array $locales, array $seen): int
    {
        $seen = array_flip($seen);
        $deleted = 0;

        Translation::query()
            ->whereIn('locale', $locales)
            ->chunkById(500, function ($rows) use ($seen, &$deleted) {
                foreach ($rows as $row) {
                    if (! isset($seen[$row->locale . '|' . $row->group . '|' . $row->key])) {
                        $row->delete();
                        $deleted++;
                    }
                }
            });

        return $deleted;
    }
}
