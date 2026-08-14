<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The rating average must come from the eager-loaded aggregate, including for
 * profiles that have no ratings — otherwise a listing page issues one query
 * per card.
 */
class RatingAggregateQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_unrated_profiles_do_not_each_cost_a_query(): void
    {
        // One profile per owner — profiles.user_id is unique.
        foreach (range(1, 5) as $ignored) {
            Profile::factory()->create([
                'user_id' => User::factory()->create(['gender' => 'female'])->id,
            ]);
        }

        $profiles = Profile::withAvg('ratings', 'percentage')->get();

        DB::enableQueryLog();

        foreach ($profiles as $profile) {
            $this->assertSame(0.0, $profile->getAveragePercentage());
        }

        $this->assertCount(0, DB::getQueryLog());

        DB::disableQueryLog();
    }

    public function test_the_eager_loaded_average_is_the_one_used(): void
    {
        $owner = User::factory()->create(['gender' => 'female']);
        $profile = Profile::factory()->create(['user_id' => $owner->id]);

        foreach ([100, 70] as $percentage) {
            $profile->rateByPercentage(
                User::factory()->create(['gender' => 'male']),
                $percentage
            );
        }

        $loaded = Profile::withAvg('ratings', 'percentage')->find($profile->id);

        DB::enableQueryLog();

        $this->assertSame(85.0, $loaded->getAveragePercentage());
        $this->assertSame(4.3, $loaded->getAverageRating());
        $this->assertCount(0, DB::getQueryLog());

        DB::disableQueryLog();
    }
}
