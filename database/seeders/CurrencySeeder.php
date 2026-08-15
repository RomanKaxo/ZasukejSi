<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

/**
 * The currencies the site ships with.
 *
 * Rates are relative to the base (CZK) and are a starting point only — they
 * are meant to be kept current from the admin. They are never applied to a
 * price a provider typed; they only offer to fill the other amounts in.
 */
class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'CZK',
                'symbol' => 'Kč',
                'name' => ['cs' => 'Koruna česká', 'en' => 'Czech koruna', 'ru' => 'Чешская крона'],
                'exchange_rate' => 1,
                'is_base' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'EUR',
                'symbol' => '€',
                'name' => ['cs' => 'Euro', 'en' => 'Euro', 'ru' => 'Евро'],
                // 1 CZK ≈ 0.04 EUR
                'exchange_rate' => 0.040000,
                'is_base' => false,
                'sort_order' => 2,
            ],
            [
                'code' => 'USD',
                'symbol' => '$',
                'name' => ['cs' => 'Americký dolar', 'en' => 'US dollar', 'ru' => 'Доллар США'],
                'exchange_rate' => 0.044000,
                'is_base' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                // is_active is left alone on re-seed: an admin may have turned
                // a currency off deliberately.
                array_merge($currency, [
                    'is_active' => Currency::where('code', $currency['code'])->value('is_active') ?? true,
                ])
            );
        }

        $this->command?->info('  ✓ ' . count($currencies) . ' měny (základní: CZK)');
    }
}
