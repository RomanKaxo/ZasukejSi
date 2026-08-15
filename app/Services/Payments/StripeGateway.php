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
        return filled(config('services.stripe.secret'));
    }

    /** Whether an incoming webhook can be verified. */
    public static function canVerifyWebhooks(): bool
    {
        return filled(config('services.stripe.webhook_secret'));
    }

    public static function client(): ?StripeClient
    {
        if (! self::isConfigured()) {
            return null;
        }

        return new StripeClient((string) config('services.stripe.secret'));
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
