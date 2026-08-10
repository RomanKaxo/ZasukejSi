<?php

namespace Database\Factories;

use App\Models\Segment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Segment>
 */
class SegmentFactory extends Factory
{
    protected $model = Segment::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ['cs' => ucfirst($name), 'en' => ucfirst($name)],
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 100000),
            'color' => fake()->hexColor(),
            'icon' => null,
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
