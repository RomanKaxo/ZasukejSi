<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Seeder;

class PopulateRatingsSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = Profile::all();
        $users = User::all();

        foreach ($profiles as $profile) {
            // Add 1-3 ratings per profile if they don't have any
            if ($profile->ratings()->count() === 0) {
                $numRatings = rand(1, 3);
                for ($i = 0; $i < $numRatings; $i++) {
                    Rating::updateOrCreate(
                        [
                            'profile_id' => $profile->id,
                            'user_id' => $users->random()->id,
                        ],
                        [
                            'rating' => rand(3, 5), // Mostly positive ratings
                            'created_at' => now(), // Rated this month
                        ]
                    );
                }
            }
        }
    }
}
