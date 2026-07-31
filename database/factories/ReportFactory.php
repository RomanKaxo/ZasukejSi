<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        $categories = Report::ALLEGATION_CATEGORIES;
        $count = fake()->numberBetween(1, 4);
        $allegations = collect($categories)->shuffle()->take($count)->values()->all();

        return [
            'profile_id' => Profile::factory(),
            'reporter_id' => User::factory()->state(['gender' => 'male']),
            'reason' => fake()->paragraphs(fake()->numberBetween(1, 3), true),
            'allegations' => $allegations,
            'blocked_at' => fake()->boolean(70) ? now() : null,
        ];
    }
}
