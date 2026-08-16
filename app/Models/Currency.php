<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Spatie\Translatable\HasTranslations;
use Throwable;

/**
 * A currency the site can quote prices in, managed from the admin.
 *
 * The rate is only ever used to *offer* a conversion. An amount a provider
 * typed is never recalculated behind their back — a drifting rate would
 * quietly change what a customer is charged.
 */
class Currency extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'code',
        'symbol',
        'name',
        'exchange_rate',
        'is_base',
        'is_active',
        'sort_order',
    ];

    public array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:6',
            'is_base' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** In-request memo; the list is read on nearly every page. */
    private static ?Collection $memo = null;

    protected static function booted(): void
    {
        static::saved(fn () => self::flush());
        static::deleted(fn () => self::flush());

        static::saving(function (Currency $currency) {
            $currency->code = strtoupper($currency->code);

            // The base currency is the yardstick, so its own rate is 1.
            if ($currency->is_base) {
                $currency->exchange_rate = 1;
            }
        });

        // Only one row can be the base.
        static::saved(function (Currency $currency) {
            if ($currency->is_base) {
                static::where('id', '!=', $currency->id)
                    ->where('is_base', true)
                    ->update(['is_base' => false]);
            }
        });
    }

    public static function flush(): void
    {
        self::$memo = null;
    }

    /**
     * Active currencies in display order.
     *
     * Never empty: before the table exists — or before it is seeded, or if an
     * admin switches the last one off — the site still has to be able to quote
     * a price, and the admin profile form still has to offer a currency to
     * pick. In those cases a single unsaved koruna row stands in.
     */
    public static function active(): Collection
    {
        if (self::$memo !== null) {
            return self::$memo;
        }

        try {
            if (! Schema::hasTable('currencies')) {
                return self::fallback();
            }

            $active = static::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            return self::$memo = $active->isEmpty() ? self::fallback() : $active;
        } catch (Throwable) {
            return self::fallback();
        }
    }

    /**
     * The one currency the site ships with, as an unsaved model.
     *
     * Not memoized — the moment a real row is inserted this must stop being
     * the answer.
     */
    private static function fallback(): Collection
    {
        return collect([
            (new self)->forceFill([
                'code' => \App\Support\Currencies::CZK,
                'symbol' => 'Kč',
                'name' => ['cs' => 'Koruna česká', 'en' => 'Czech koruna', 'ru' => 'Чешская крона'],
                'exchange_rate' => 1,
                'is_base' => true,
                'is_active' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public static function base(): ?self
    {
        return self::active()->firstWhere('is_base', true) ?? self::active()->first();
    }

    public static function findByCode(string $code): ?self
    {
        return self::active()->firstWhere('code', strtoupper($code));
    }

    /** @return array<string, string> code => symbol */
    public static function symbols(): array
    {
        return self::active()->pluck('symbol', 'code')->all();
    }

    /** @return array<string, string> code => "Koruna (Kč)" */
    public static function options(): array
    {
        return self::active()
            ->mapWithKeys(fn (self $c) => [$c->code => $c->getTranslation('name', app()->getLocale()) . ' (' . $c->symbol . ')'])
            ->all();
    }

    /**
     * Convert an amount from one currency to another through the base.
     *
     * Returns null when either side is unknown, so a caller can tell "cannot
     * convert" from "converts to zero".
     */
    public static function convert(float $amount, string $from, string $to): ?float
    {
        $fromCurrency = self::findByCode($from);
        $toCurrency = self::findByCode($to);

        if (! $fromCurrency || ! $toCurrency) {
            return null;
        }

        $fromRate = (float) $fromCurrency->exchange_rate;
        $toRate = (float) $toCurrency->exchange_rate;

        if ($fromRate <= 0) {
            return null;
        }

        return round($amount / $fromRate * $toRate, 2);
    }

    /** Amounts written the way each currency normally is. */
    public function format(float|int|string|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }

        $value = (float) $amount;
        $decimals = fmod($value, 1.0) === 0.0 ? 0 : 2;

        // Korunas trail their symbol and group with spaces; the others lead.
        $trailing = in_array($this->code, ['CZK', 'PLN', 'HUF', 'SEK', 'NOK', 'DKK'], true);

        $formatted = number_format(
            $value,
            $decimals,
            $trailing ? ',' : '.',
            $trailing ? ' ' : ',',
        );

        return $trailing
            ? $formatted . ' ' . $this->symbol
            : $this->symbol . $formatted;
    }
}
