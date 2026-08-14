<?php

namespace Database\Seeders;

use App\Models\SubscriptionType;
use Illuminate\Database\Seeder;

/**
 * Premium membership plans sold to members.
 *
 * These are what the design's locked ratings, the "Premium účet vám odemkne
 * hodnocení" bar and the "Vaše Premium členství platí do …" banner refer to.
 * They live in `subscription_types` alongside the provider VIP tiers and are
 * told apart by `audience`.
 */
class MemberSubscriptionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'premium-month',
                'name' => ['cs' => 'Premium na měsíc', 'en' => 'Premium monthly'],
                'description' => [
                    'cs' => 'Odemkne hodnocení dívek a přístup do archivu.',
                    'en' => 'Unlocks profile ratings and archive access.',
                ],
                // Flat key => label, matching SubscriptionTypeSeeder — the plan
                // views iterate the values directly.
                'features' => [
                    'ratings' => 'Hodnocení dívek',
                    'archive' => 'Archiv dívek',
                    'girls_of_month' => 'Dívky měsíce',
                ],
                'price' => 299,
                'duration_days' => 30,
                'sort_order' => 1,
            ],
            [
                'slug' => 'premium-quarter',
                'name' => ['cs' => 'Premium na čtvrt roku', 'en' => 'Premium quarterly'],
                'description' => [
                    'cs' => 'Tři měsíce přístupu se slevou.',
                    'en' => 'Three months of access at a discount.',
                ],
                // Flat key => label, matching SubscriptionTypeSeeder — the plan
                // views iterate the values directly.
                'features' => [
                    'ratings' => 'Hodnocení dívek',
                    'archive' => 'Archiv dívek',
                    'girls_of_month' => 'Dívky měsíce',
                ],
                'price' => 749,
                'duration_days' => 90,
                'sort_order' => 2,
            ],
            [
                'slug' => 'premium-year',
                'name' => ['cs' => 'Premium na rok', 'en' => 'Premium yearly'],
                'description' => [
                    'cs' => 'Celý rok přístupu za nejlepší cenu.',
                    'en' => 'A full year of access at the best price.',
                ],
                // Flat key => label, matching SubscriptionTypeSeeder — the plan
                // views iterate the values directly.
                'features' => [
                    'ratings' => 'Hodnocení dívek',
                    'archive' => 'Archiv dívek',
                    'girls_of_month' => 'Dívky měsíce',
                ],
                'price' => 2490,
                'duration_days' => 365,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionType::updateOrCreate(
                ['slug' => $plan['slug']],
                [
                    'audience' => SubscriptionType::AUDIENCE_MEMBER,
                    'name' => $plan['name'],
                    'description' => $plan['description'],
                    'features' => $plan['features'],
                    'price' => $plan['price'],
                    'duration_days' => $plan['duration_days'],
                    'color' => 'warning',
                    'icon' => 'heroicon-o-sparkles',
                    'sort_order' => $plan['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
