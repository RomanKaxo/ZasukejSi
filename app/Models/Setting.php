<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Operator-editable configuration.
 *
 * Values fall back to config/site.php when the row is absent, so the system
 * keeps working before the table is seeded — and during the migration run
 * itself, when the table does not exist yet.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** In-request memo so a page render hits the table at most once. */
    private static ?array $cache = null;

    /**
     * Latched only when the table is confirmed present. A negative result is
     * never cached: under RefreshDatabase the first call happens before the
     * migrations have run.
     */
    private static bool $tableAvailable = false;

    public static function get(string $key, mixed $default = null): mixed
    {
        if (! self::tableAvailable()) {
            return $default;
        }

        if (self::$cache === null) {
            try {
                self::$cache = self::query()->pluck('value', 'key')->all();
            } catch (Throwable) {
                return $default;
            }
        }

        return self::$cache[$key] ?? $default;
    }

    public static function getInt(string $key, int $default): int
    {
        $value = self::get($key);

        return is_numeric($value) ? (int) $value : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => (string) $value]);

        self::flush();
    }

    public static function flush(): void
    {
        self::$cache = null;
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::flush());
        static::deleted(fn () => self::flush());
    }

    private static function tableAvailable(): bool
    {
        if (self::$tableAvailable) {
            return true;
        }

        try {
            $available = Schema::hasTable('settings');
        } catch (Throwable) {
            return false;
        }

        return $available ? (self::$tableAvailable = true) : false;
    }
}
