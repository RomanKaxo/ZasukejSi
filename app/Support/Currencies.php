<?php

namespace App\Support;

/**
 * The currencies a plan can be priced in, and which one a locale gets.
 *
 * Prices are entered per currency rather than converted at display time — a
 * rate that drifts would quietly misquote the price to the customer.
 */
class Currencies
{
    public const CZK = 'CZK';
    public const EUR = 'EUR';
    public const USD = 'USD';

    /** @return array<string, string> */
    public static function all(): array
    {
        return [
            self::CZK => 'Kč',
            self::EUR => '€',
            self::USD => '$',
        ];
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
        return match ($locale ?? app()->getLocale()) {
            'cs' => self::CZK,
            'ru' => self::USD,
            default => self::EUR,
        };
    }

    /**
     * Format an amount the way the currency is normally written: korunas after
     * the number, euros and dollars before it.
     */
    public static function format(float|int|string|null $amount, string $currency): string
    {
        if ($amount === null) {
            return '';
        }

        $currency = strtoupper($currency);
        $value = (float) $amount;
        $hasFraction = fmod($value, 1.0) !== 0.0;

        $formatted = number_format(
            $value,
            $hasFraction ? 2 : 0,
            $currency === self::CZK ? ',' : '.',
            $currency === self::CZK ? ' ' : ',',
        );

        return $currency === self::CZK
            ? $formatted . ' ' . self::symbol($currency)
            : self::symbol($currency) . $formatted;
    }
}
