<?php

namespace App\Support;

use App\Models\Currency;

/**
 * Thin façade over the `currencies` table.
 *
 * The list used to be hardcoded here, so adding a currency — or turning one
 * off — took a deploy. Callers keep the same API; the data moved.
 *
 * The constants remain because they name the currencies the site ships with
 * and are used as defaults; they are not a limit on what can exist.
 */
class Currencies
{
    public const CZK = 'CZK';
    public const EUR = 'EUR';
    public const USD = 'USD';

    /** @return array<string, string> code => symbol */
    public static function all(): array
    {
        $symbols = Currency::symbols();

        // Before the table is seeded the app still has to quote something.
        return $symbols === [] ? [self::CZK => 'Kč'] : $symbols;
    }

    /** @return array<int, string> */
    public static function codes(): array
    {
        return array_keys(self::all());
    }

    public static function symbol(string $currency): string
    {
        return self::all()[strtoupper($currency)] ?? strtoupper($currency);
    }

    public static function isSupported(string $currency): bool
    {
        return array_key_exists(strtoupper($currency), self::all());
    }

    /** The currency a given site language is quoted in. */
    public static function forLocale(?string $locale = null): string
    {
        $preferred = match ($locale ?? app()->getLocale()) {
            'cs' => self::CZK,
            'ru' => self::USD,
            default => self::EUR,
        };

        // An admin may have switched that currency off; fall back to the base
        // rather than quoting in something not on offer.
        if (self::isSupported($preferred)) {
            return $preferred;
        }

        return Currency::base()?->code ?? self::CZK;
    }

    public static function format(float|int|string|null $amount, string $currency): string
    {
        if ($amount === null) {
            return '';
        }

        $model = Currency::findByCode($currency);

        if ($model) {
            return $model->format($amount);
        }

        // Unknown currency: still print something readable.
        $value = (float) $amount;
        $decimals = fmod($value, 1.0) === 0.0 ? 0 : 2;

        return number_format($value, $decimals, ',', ' ') . ' ' . strtoupper($currency);
    }

    /** @see Currency::convert() */
    public static function convert(float $amount, string $from, string $to): ?float
    {
        return Currency::convert($amount, $from, $to);
    }
}
