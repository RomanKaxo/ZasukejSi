<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Rating;
use App\Models\User;
use App\Support\RatingScale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rating>
 *
 * The percentage is the stored truth; `rating` is derived from it so factory
 * rows can never contradict the two columns the way hand-written ones could.
 */
class RatingFactory extends Factory
{
    protected $model = Rating::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'profile_id' => Profile::factory(),
            'user_id' => User::factory(),
            'percentage' => fake()->numberBetween(1, 100),
        ];
    }

    /** Build a rating from a percentage, keeping the star mirror in step. */
    public function percentage(int $percentage): static
    {
        return $this->state(fn () => ['percentage' => RatingScale::clamp($percentage)]);
    }

    /** Create a positive rating. */
    public function positive(): static
    {
        return $this->state(fn () => ['percentage' => fake()->numberBetween(80, 100)]);
    }

    /** Create a neutral rating. */
    public function neutral(): static
    {
        return $this->state(fn () => ['percentage' => 60]);
    }

    /** Create a negative rating. */
    public function negative(): static
    {
        return $this->state(fn () => ['percentage' => fake()->numberBetween(1, 39)]);
    }

    public function configure(): static
    {
        // The model keeps `rating` in step on save; this only covers models
        // that are made but never persisted.
        return $this->afterMaking(function (Rating $rating) {
            if ($rating->percentage > 0) {
                $rating->rating = RatingScale::toWholeStars((float) $rating->percentage);
            }
        });
    }
}
