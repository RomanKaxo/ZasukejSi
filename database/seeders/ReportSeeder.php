<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $reporter = User::where('email', 'user@example.com')->first();

        if (! $reporter) {
            return;
        }

        if (Report::where('reporter_id', $reporter->id)->exists()) {
            return;
        }

        $profiles = Profile::approved()->public()->inRandomOrder()->limit(3)->get();

        foreach ($profiles as $profile) {
            Report::factory()->create([
                'profile_id' => $profile->id,
                'reporter_id' => $reporter->id,
            ]);
        }
    }
}
