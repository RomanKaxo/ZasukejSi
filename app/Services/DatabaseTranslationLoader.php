<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Translation\FileLoader;
use Throwable;

/**
 * Translation loader that lets the database override the lang files.
 *
 * Files remain the source of defaults — they are what ships, what
 * `translations:audit` checks and what a fresh install falls back to. Rows in
 * `translations` are an override layer merged on top, so an operator can
 * reword any string from the admin without a deploy.
 *
 * Overrides are memoised per locale+group; Translation::flushCache() clears the
 * relevant entry whenever a row is saved or deleted.
 */
class DatabaseTranslationLoader extends FileLoader
{
    /**
     * Groups already merged in this request, keyed "locale|group".
     *
     * @var array<string, array<string, mixed>>
     */
    private array $memo = [];

    /**
     * Set once the translations table is known to exist. Before migrations run
     * — and during `migrate:fresh` — there is nothing to read, and querying
     * would abort the very command that creates the table.
     */
    private ?bool $tableAvailable = null;

    /**
     * Drop the in-request memo.
     *
     * The Cache entry alone is not enough: this loader memoises per request and
     * the Translator keeps its own map of already-loaded groups. Without
     * clearing both, an edit made in the admin would not show until the next
     * request. Called from Translation::flushCache().
     */
    public function forgetMemo(?string $locale = null, ?string $group = null): void
    {
        if ($locale === null || $group === null) {
            $this->memo = [];

            return;
        }

        unset($this->memo[$locale . '|' . $group]);
    }

    public function load($locale, $group, $namespace = null)
    {
        $lines = parent::load($locale, $group, $namespace);

        // Package namespaces (`vendor::group`) are not editable here.
        if ($namespace !== null && $namespace !== '*') {
            return $lines;
        }

        $overrides = $this->overridesFor($locale, $group);

        if ($overrides === []) {
            return $lines;
        }

        foreach ($overrides as $key => $value) {
            // Arr::set understands the dot path, so "landing.advert.title"
            // lands in the right nested position.
            Arr::set($lines, $key, $value);
        }

        return $lines;
    }

    /**
     * @return array<string, string>
     */
    private function overridesFor(string $locale, string $group): array
    {
        $memoKey = $locale . '|' . $group;

        if (array_key_exists($memoKey, $this->memo)) {
            return $this->memo[$memoKey];
        }

        // Not memoised: the table may simply not exist *yet* (the loader is a
        // singleton resolved at boot, and in tests migrations run afterwards on
        // the same in-memory database). Caching "no overrides" here would make
        // that permanent for the whole process.
        if (! $this->translationsTableAvailable()) {
            return [];
        }

        try {
            $overrides = Cache::rememberForever(
                Translation::cacheKey($locale, $group),
                fn () => Translation::query()
                    ->forLocale($locale)
                    ->forGroup($group)
                    ->whereNotNull('value')
                    ->pluck('value', 'key')
                    ->all()
            );
        } catch (Throwable $e) {
            // Never let a translation lookup take the page down: fall back to
            // the file values, which are always present.
            $overrides = [];
        }

        return $this->memo[$memoKey] = $overrides;
    }

    /**
     * Only a positive result is remembered.
     *
     * A table can appear during the life of the process — migrations run after
     * the container boots, which is exactly what happens under RefreshDatabase —
     * but once it exists it never goes away, so `true` is safe to latch.
     */
    private function translationsTableAvailable(): bool
    {
        if ($this->tableAvailable === true) {
            return true;
        }

        try {
            $available = \Illuminate\Support\Facades\Schema::hasTable('translations');
        } catch (Throwable $e) {
            // No database connection at all (e.g. `config:cache` during build).
            return false;
        }

        return $available ? ($this->tableAvailable = true) : false;
    }
}
