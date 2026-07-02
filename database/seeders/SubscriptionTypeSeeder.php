<?php

namespace Database\Seeders;

use App\Models\SubscriptionType;
use Illuminate\Database\Seeder;

class SubscriptionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => ['en' => 'VIP', 'cs' => 'VIP'],
                'slug' => 'vip',
                'description' => [
                    'en' => 'Basic VIP membership with profile highlighting',
                    'cs' => 'Základní VIP členství se zvýrazněním profilu',
                ],
                'features' => [
                    'profile_highlight' => 'Profile highlighting in listings',
                    'priority_support' => 'Priority support',
                ],
                'price' => 9.99,
                'duration_days' => 30,
                'color' => 'warning',
                'icon' => 'heroicon-o-star',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => ['en' => 'Premium', 'cs' => 'Premium'],
                'slug' => 'premium',
                'description' => [
                    'en' => 'Premium membership with top placement and extra features',
                    'cs' => 'Prémiové členství s umístěním na začátku a dalšími funkcemi',
                ],
                'features' => [
                    'top_placement' => 'Profile appears first in search results',
                    'profile_highlight' => 'Profile highlighting in listings',
                    'verified_badge' => 'Premium verified badge',
                    'priority_support' => 'Priority support',
                    'analytics' => 'Profile view analytics',
                ],
                'price' => 24.99,
                'duration_days' => 30,
                'color' => 'success',
                'icon' => 'heroicon-o-sparkles',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => ['en' => 'Elite', 'cs' => 'Elite'],
                'slug' => 'elite',
                'description' => [
                    'en' => 'Elite membership with all features and premium support',
                    'cs' => 'Elitní členství se všemi funkcemi a prémiovou podporou',
                ],
                'features' => [
                    'top_placement' => 'Profile appears first in search results',
                    'profile_highlight' => 'Gold profile highlighting',
                    'verified_badge' => 'Elite verified badge',
                    'priority_support' => '24/7 Priority support',
                    'analytics' => 'Advanced profile analytics',
                    'featured_homepage' => 'Featured on homepage',
                    'social_boost' => 'Social media promotion',
                ],
                'price' => 49.99,
                'duration_days' => 30,
                'color' => 'danger',
                'icon' => 'heroicon-o-fire',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => ['en' => 'Premium Yearly', 'cs' => 'Premium Roční'],
                'slug' => 'premium-yearly',
                'description' => [
                    'en' => 'Premium membership for a full year at a discounted price',
                    'cs' => 'Prémiové členství na celý rok za zvýhodněnou cenu',
                ],
                'features' => [
                    'top_placement' => 'Profile appears first in search results',
                    'profile_highlight' => 'Profile highlighting in listings',
                    'verified_badge' => 'Premium verified badge',
                    'priority_support' => 'Priority support',
                    'analytics' => 'Profile view analytics',
                    'yearly_discount' => '2 months free (save 17%)',
                ],
                'price' => 249.99,
                'duration_days' => 365,
                'color' => 'info',
                'icon' => 'heroicon-o-trophy',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            SubscriptionType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
