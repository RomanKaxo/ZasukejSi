<?php

namespace App\Support;

use App\Models\Setting;
use App\Services\Payments\StripeGateway;

/**
 * Walking the order through when there is no payment gateway.
 *
 * Without Stripe keys the checkout stopped at „Platby nejsou momentálně
 * dostupné" and the plans could not be bought at all — which made the whole
 * flow, including the confirmation screen and the state that follows it,
 * impossible to see or test.
 *
 * So the order completes without a payment. Two things keep that honest:
 *
 *   · it only engages when Stripe is genuinely not configured, so adding the
 *     keys switches real payments back on by itself;
 *   · every subscription it creates is marked `manual`, so an operator can
 *     always tell which ones were never paid for.
 *
 * The admin switch exists to turn it off on a deployment that has no keys yet
 * and must not hand out memberships in the meantime.
 */
class OfflineCheckout
{
    public const KEY = 'payments.offline_checkout';

    /** Marker written into the subscription's metadata. */
    public const SOURCE = 'manual';

    public static function isEnabled(): bool
    {
        return Setting::getBool(self::KEY, true);
    }

    /** Whether this order should be completed without taking money. */
    public static function shouldHandle(): bool
    {
        return ! StripeGateway::isConfigured() && self::isEnabled();
    }

    /**
     * Metadata stamped onto a subscription created this way.
     *
     * @return array<string, mixed>
     */
    public static function metadata(): array
    {
        return [
            'source' => self::SOURCE,
            'paid' => false,
            'granted_at' => now()->toIso8601String(),
        ];
    }

    /** Whether a stored subscription came through this path. */
    public static function wasManual(?array $metadata): bool
    {
        return ($metadata['source'] ?? null) === self::SOURCE;
    }
}
