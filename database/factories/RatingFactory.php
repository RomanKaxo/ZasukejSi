<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as FakerFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rating>
 */
class RatingFactory extends Factory
{
    protected $model = Rating::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ensure Faker is available (important for production)
        if (!function_exists('fake')) {
            $faker = FakerFactory::create();
        } else {
            $faker = fake();
        }
        
        return [
            'profile_id' => Profile::factory(),
            'user_id' => User::factory(),
            'rating' => $faker->numberBetween(1, 5),
        ];
    }

    /**
     * Create a positive rating (4-5 stars).
     */
    public function positive(): static
    {
        return $this->state(function (array $attributes) {
            if (!function_exists('fake')) {
                $faker = FakerFactory::create();
            } else {
                $faker = fake();
            }
            return [
                'rating' => $faker->numberBetween(4, 5),
            ];
        });
    }

    /**
     * Create a neutral rating (3 stars).
     */
    public function neutral(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 3,
        ]);
    }

    /**
     * Create a negative rating (1-2 stars).
     */
    public function negative(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->numberBetween(1, 2),
        ]);
    }
}
