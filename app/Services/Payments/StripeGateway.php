<?php

namespace App\Services\Payments;

use Stripe\StripeClient;

/**
 * One place that decides whether Stripe is usable.
 *
 * `new StripeClient(null)` throws "$config must be a string or an array",
 * which reached the customer as a 500 on the checkout route whenever the keys
 * were missing from the environment. A missing key is a configuration problem,
 * not something the buyer did, and it has to read that way.
 */
class StripeGateway
{
    public static function isConfigured(): bool
    {
        return filled(self::secret());
    }

    /** Whether an incoming webhook can be verified. */
    public static function canVerifyWebhooks(): bool
    {
        return filled(self::webhookSecret());
    }

    public static function client(): ?StripeClient
    {
        if (! self::isConfigured()) {
            return null;
        }

        return new StripeClient((string) self::secret());
    }

    /**
     * The secret key, from the admin if it is set there.
     *
     * The environment file stays the fallback and the deployment default; what
     * changed is that correcting a key no longer needs shell access. The
     * person who notices that payments stopped is rarely the person who can
     * edit `.env`.
     */
    public static function secret(): ?string
    {
        return self::fromAdmin('secret_key') ?? config('services.stripe.secret');
    }

    public static function webhookSecret(): ?string
    {
        return self::fromAdmin('webhook_secret') ?? config('services.stripe.webhook_secret');
    }

    private static function fromAdmin(string $key): ?string
    {
        try {
            $method = \App\Services\Payments\PaymentMethods::find(\App\Models\PaymentMethod::CODE_STRIPE);
        } catch (\Throwable) {
            // Před migracemi tabulka neexistuje; konfigurace ze souboru platí.
            return null;
        }

        $value = $method?->setting($key);

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    /**
     * The lowest denomination Stripe charges in — cents, haléře and so on.
     * Zero-decimal currencies must not be multiplied, or the customer is
     * charged a hundred times the price.
     */
    public static function amountInMinorUnits(float $amount, string $currency): int
    {
        $zeroDecimal = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];

        return in_array(strtoupper($currency), $zeroDecimal, true)
            ? (int) round($amount)
            : (int) round($amount * 100);
    }
}
