<?php

namespace App\Console\Commands;

use App\Models\Translation;
use Illuminate\Console\Command;

/**
 * Throw away every cached translation override.
 *
 * The escape hatch for a value stuck in the cache with nothing behind it — the
 * site showing a text that exists neither in the lang files nor in the
 * database, because the row it came from was removed by something that fires
 * no model events.
 *
 * Separate from `cache:clear` on purpose: that would also drop the country
 * counts, the settings and everything else the site memoises, which is a
 * heavier hammer than „ten překlad je špatně".
 */
class TranslationsFlushCommand extends Command
{
    protected $signature = 'translations:flush';

    protected $description = 'Drop cached translation overrides so the files and the database decide again';

    public function handle(): int
    {
        Translation::flushAll();

        $this->info('Mezipaměť překladů vyprázdněna. Platí soubory a databáze.');

        return self::SUCCESS;
    }
}
