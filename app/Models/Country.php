<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Spatie\Translatable\HasTranslations;

/**
 * Which countries appear in the public country lists, in what order.
 *
 * Names are not stored here by default — lang/cs/codes.php and lang/en/codes.php
 * already carry all 306 ISO countries in both locales. `name_override` exists
 * only for the cases where the site wants to say something different from the
 * standard name.
 *
 * Regions and profile counts are never stored: see CountryStatsService.
 */
class Country extends Model
{
    use HasFactory, HasTranslations;

    /** @var array<int, string> */
    public $translatable = ['name_override'];

    /** @var array<int, string> */
    protected $fillable = [
        'code',
        'name_override',
        'sort_order',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Codes are stored uppercase so they join cleanly against
     * `profiles.country_code` and `cities.country_code`.
     */
    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = strtoupper((string) $value);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }

    /**
     * Display name in the current locale: the override when set, otherwise the
     * standard ISO name from lang/{locale}/codes.php, otherwise the raw code.
     */
    public function getDisplayNameAttribute(): string
    {
        $override = $this->getTranslation('name_override', app()->getLocale(), false);

        if (filled($override)) {
            return $override;
        }

        return static::isoName($this->code);
    }

    /**
     * Standard country name for a code, from the translation files that already
     * ship with the project. Falls back to the uppercase code so an unknown or
     * newly added code never renders as an empty string.
     */
    public static function isoName(string $code): string
    {
        $key = 'codes.' . strtolower($code);

        return Lang::has($key) ? __($key) : strtoupper($code);
    }
}
