<?php

namespace Database\Seeders;

use App\Models\SubscriptionType;
use Illuminate\Database\Seeder;

/**
 * Premium plans for members — the audience that unlocks profile ratings.
 *
 * Prices are entered per currency rather than converted at display time, so a
 * moving exchange rate cannot misquote what the customer is charged. The
 * euro and dollar amounts are round numbers of their own, not the koruna
 * price divided by a rate.
 */
class MemberSubscriptionTypeSeeder extends Seeder
{
    /** Shared by every member plan; flat key => label, as the views expect. */
    private const FEATURES = [
        'ratings' => 'Hodnocení dívek',
        'archive' => 'Archiv dívek',
        'girls_of_month' => 'Dívky měsíce',
    ];

    public function run(): void
    {
        $plans = [
            [
                'slug' => 'premium-30',
                'name' => ['cs' => 'Premium na 30 dní', 'en' => 'Premium 30 days', 'ru' => 'Premium 30 дней'],
                'description' => ['cs' => 'Vyzkoušejte si vše na měsíc.', 'en' => 'Try everything for a month.', 'ru' => 'Попробуйте всё на месяц.'],
                'days' => 30,
                'czk' => 299,
                'eur' => 12,
                'usd' => 13,
                'sort' => 1,
            ],
            [
                'slug' => 'premium-60',
                'name' => ['cs' => 'Premium na 60 dní', 'en' => 'Premium 60 days', 'ru' => 'Premium 60 дней'],
                'description' => ['cs' => 'Dva měsíce s mírnou slevou.', 'en' => 'Two months at a small discount.', 'ru' => 'Два месяца со скидкой.'],
                'days' => 60,
                'czk' => 549,
                'eur' => 22,
                'usd' => 24,
                'sort' => 2,
            ],
            [
                'slug' => 'premium-90',
                'name' => ['cs' => 'Premium na 90 dní', 'en' => 'Premium 90 days', 'ru' => 'Premium 90 дней'],
                'description' => ['cs' => 'Čtvrtletní přístup ke všem funkcím.', 'en' => 'A quarter of access to everything.', 'ru' => 'Квартальный доступ ко всему.'],
                'days' => 90,
                'czk' => 749,
                'eur' => 30,
                'usd' => 33,
                'sort' => 3,
            ],
            [
                'slug' => 'premium-180',
                'name' => ['cs' => 'Premium na 180 dní', 'en' => 'Premium 180 days', 'ru' => 'Premium 180 дней'],
                'description' => ['cs' => 'Půl roku za výhodnější cenu.', 'en' => 'Half a year at a better price.', 'ru' => 'Полгода по выгодной цене.'],
                'days' => 180,
                'czk' => 1390,
                'eur' => 56,
                'usd' => 61,
                'sort' => 4,
            ],
            [
                'slug' => 'premium-365',
                'name' => ['cs' => 'Premium na rok', 'en' => 'Premium yearly', 'ru' => 'Premium на год'],
                'description' => ['cs' => 'Celý rok přístupu za nejlepší cenu.', 'en' => 'A full year of access at the best price.', 'ru' => 'Целый год доступа по лучшей цене.'],
                'days' => 365,
                'czk' => 2490,
                'eur' => 99,
                'usd' => 109,
                'sort' => 5,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionType::updateOrCreate(
                ['slug' => $plan['slug']],
                [
                    'audience' => SubscriptionType::AUDIENCE_MEMBER,
                    'name' => $plan['name'],
                    'description' => $plan['description'],
                    'features' => self::FEATURES,
                    // `price` stays the Czech amount for anything still
                    // reading the legacy column.
                    'price' => $plan['czk'],
                    'price_czk' => $plan['czk'],
                    'price_eur' => $plan['eur'],
                    'price_usd' => $plan['usd'],
                    'duration_days' => $plan['days'],
                    'color' => 'warning',
                    'icon' => 'heroicon-o-sparkles',
                    'sort_order' => $plan['sort'],
                    'is_active' => true,
                ]
            );
        }

        // The first three plans shipped under different slugs; retiring them
        // keeps the page from listing two of everything.
        SubscriptionType::whereIn('slug', ['premium-month', 'premium-quarter', 'premium-year'])
            ->update(['is_active' => false]);

        $this->command?->info('  ✓ ' . count($plans) . ' členských plánů (30/60/90/180/365 dní, CZK/EUR/USD)');
    }
}
