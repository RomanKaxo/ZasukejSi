<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * One editable string.
 *
 * `group` is the lang file the key belongs to ("front", "profiles", …) or the
 * reserved "*" for JSON translations. `key` is the dot path inside that file.
 *
 * Saving or deleting a row flushes the loader cache, so a change in the admin
 * shows on the site immediately.
 */
class Translation extends Model
{
    use HasFactory;

    /** Group for JSON translations, i.e. those keyed by the sentence itself. */
    public const JSON_GROUP = '*';

    protected $fillable = [
        'locale',
        'group',
        'key',
        'value',
        'default_value',
    ];

    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    public function scopeForGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Rows the admin has actually changed away from the shipped default.
     */
    public function scopeOverridden($query)
    {
        return $query->whereNotNull('value')
            ->where(function ($q) {
                $q->whereNull('default_value')
                    ->orWhereColumn('value', '!=', 'default_value');
            });
    }

    public function isOverridden(): bool
    {
        return $this->value !== null && $this->value !== $this->default_value;
    }

    /**
     * The full key as it appears in the code, e.g. `front.nav.home`.
     */
    public function getFullKeyAttribute(): string
    {
        return $this->group === self::JSON_GROUP
            ? $this->key
            : $this->group . '.' . $this->key;
    }

    /**
     * Drop the loader's memoised groups so the site picks the change up at once.
     */
    public static function flushCache(?string $locale = null, ?string $group = null): void
    {
        if ($locale !== null && $group !== null) {
            Cache::forget(self::cacheKey($locale, $group));
        } else {
            // A broad change invalidates everything by moving the version the
            // keys are built from.
            //
            // This used to walk the groups still present in the table and
            // forget those — which does nothing at all for the case that
            // matters, namely rows that have just been *removed*. An override
            // deleted outside a model event (a truncate, a seed, a bulk query)
            // therefore stayed cached forever, and the site went on showing a
            // text that existed nowhere: in the file, in the database, or in
            // anybody's memory of having typed it.
            Cache::forever(self::VERSION_KEY, (string) now()->getTimestampMs());
        }

        self::flushInMemory($locale, $group);
    }

    /**
     * Drop every cached override, whatever the table says.
     *
     * The escape hatch for exactly the situation above: an old value stuck in
     * the cache with nothing behind it.
     */
    public static function flushAll(): void
    {
        self::flushCache();
    }

    /**
     * Clear the two in-request caches that sit above the Cache store.
     *
     * The loader memoises groups it has already merged, and the Translator
     * keeps its own map of loaded groups and will not ask the loader again.
     * Without resetting both, an edit saved in the admin would not be visible
     * until the next request.
     */
    private static function flushInMemory(?string $locale, ?string $group): void
    {
        $loader = app('translation.loader');

        if ($loader instanceof \App\Services\DatabaseTranslationLoader) {
            $loader->forgetMemo($locale, $group);
        }

        $translator = app('translator');

        if (method_exists($translator, 'setLoaded')) {
            $translator->setLoaded([]);
        }
    }

    /** Bumped on any broad change; part of every cache key. */
    private const VERSION_KEY = 'translations:version';

    public static function cacheKey(string $locale, string $group): string
    {
        return 'translations:' . self::cacheVersion() . ":{$locale}:{$group}";
    }

    private static function cacheVersion(): string
    {
        try {
            return (string) Cache::rememberForever(self::VERSION_KEY, fn () => '1');
        } catch (\Throwable) {
            // Bez funkční cache se prostě nekešuje; překlady se načtou ze
            // souborů a z databáze pokaždé znovu.
            return '1';
        }
    }

    protected static function booted(): void
    {
        $flush = static fn (Translation $translation) => self::flushCache($translation->locale, $translation->group);

        static::saved($flush);
        static::deleted($flush);
    }
}
