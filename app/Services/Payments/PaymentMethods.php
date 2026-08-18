<?php

namespace App\Services\Payments;

use App\Models\PaymentMethod;
use Illuminate\Support\Collection;

/**
 * The methods this application knows how to take money with.
 *
 * The registry is in code and the configuration is in the database, and the
 * split matters: an operator can switch a method on, correct a key or change
 * the bank account without a deploy, but cannot invent a method by typing a
 * new code into a row — there would be nothing behind it.
 */
class PaymentMethods
{
    /**
     * @return array<string, array{label: string, description: string}>
     */
    public static function known(): array
    {
        return [
            PaymentMethod::CODE_STRIPE => [
                'label' => 'Platební karta (Stripe)',
                'description' => 'Zaplatí se hned a předplatné se aktivuje samo.',
            ],
            PaymentMethod::CODE_BANK_TRANSFER => [
                'label' => 'Bankovní převod',
                'description' => 'Objednávka počká, než peníze dorazí. Platbu potvrdíte v administraci.',
            ],
        ];
    }

    public static function label(string $code): string
    {
        return self::known()[$code]['label'] ?? $code;
    }

    /**
     * Methods a buyer may actually choose from.
     *
     * Usable, not merely enabled: a bank transfer without an account number is
     * a page telling somebody to send money nowhere, and offering it would be
     * worse than offering nothing.
     *
     * @return Collection<int, PaymentMethod>
     */
    public static function available(): Collection
    {
        if (! self::ready()) {
            return collect();
        }

        return PaymentMethod::query()
            ->enabled()
            ->ordered()
            ->get()
            ->filter(fn (PaymentMethod $method) => $method->isUsable()
                && array_key_exists($method->code, self::known()))
            ->values();
    }

    public static function find(string $code): ?PaymentMethod
    {
        if (! self::ready()) {
            return null;
        }

        return PaymentMethod::query()->where('code', $code)->first();
    }

    /**
     * Whether the configuration is readable at all.
     *
     * Between deploying the code and running the migrations the table does not
     * exist yet, and that is a normal few minutes in the life of every
     * release. Letting the query through turned it into a 500 — not only on
     * the admin page, which would be bad enough, but on the page where a
     * customer buys a subscription, which is worse and had nothing to do with
     * payment methods before that morning.
     *
     * Without the table there are simply no configured methods, and the
     * checkout falls back to what it did before this feature existed.
     */
    public static function ready(): bool
    {
        try {
            // Bez zapamatování. Původně se kladná odpověď držela ve statické
            // proměnné s odůvodněním, že tabulka může vzniknout, ale ne
            // zmizet — což je pravda v provozu a nepravda v testech, kde ji
            // jeden test zahodí a další v témže procesu si pak myslí, že tam
            // pořád je. Dotaz navíc je levnější než tahle třída chyb.
            return \Illuminate\Support\Facades\Schema::hasTable('payment_methods');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function isAvailable(string $code): bool
    {
        return self::available()->contains(fn (PaymentMethod $method) => $method->code === $code);
    }

    /**
     * The method to use when the buyer did not choose.
     *
     * With one method there is nothing to choose; with several, the first in
     * the operator's own order is the sensible default.
     */
    public static function default(): ?PaymentMethod
    {
        return self::available()->first();
    }

    /** Ensure every known method has a row, so the admin has something to edit. */
    public static function sync(): void
    {
        if (! self::ready()) {
            return;
        }

        foreach (array_keys(self::known()) as $index => $code) {
            PaymentMethod::firstOrCreate(
                ['code' => $code],
                ['is_enabled' => false, 'sort_order' => $index],
            );
        }
    }
}
