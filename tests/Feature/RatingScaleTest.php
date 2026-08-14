<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Rating;
use App\Models\Setting;
use App\Models\User;
use App\Support\RatingScale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ratings are collected as a percentage and stored as one. Squeezing them into
 * 1-5 stars first made star values 3 and 1 unreachable and drew a 70% rating
 * as 80% wherever it was converted back.
 */
class RatingScaleTest extends TestCase
{
    use RefreshDatabase;

    private function profileAndMember(): array
    {
        $owner = User::factory()->create(['gender' => 'female']);
        $profile = Profile::factory()->create(['user_id' => $owner->id]);
        $member = User::factory()->create(['gender' => 'male']);

        return [$profile, $member];
    }

    public function test_the_chosen_percentage_is_what_gets_stored(): void
    {
        [$profile, $member] = $this->profileAndMember();

        $this->assertSame(Profile::RATE_OK, $profile->rateByPercentage($member, 70));

        $rating = Rating::where('profile_id', $profile->id)->first();

        $this->assertSame(70, $rating->percentage);
        $this->assertSame(70.0, $profile->fresh()->getAveragePercentage());
    }

    public function test_seventy_percent_is_no_longer_displayed_as_eighty(): void
    {
        [$profile, $member] = $this->profileAndMember();

        $profile->rateByPercentage($member, 70);

        // 70% on the 1-5 scale is 3.5, not the 4 stars (80%) it used to become.
        $this->assertSame(3.5, $profile->fresh()->getAverageRating());
    }

    public function test_star_values_three_and_one_are_reachable(): void
    {
        [$profile, $member] = $this->profileAndMember();
        $second = User::factory()->create(['gender' => 'male']);

        $profile->rateByPercentage($member, 70);
        $profile->rateByPercentage($second, 50);

        // Average 60% => exactly 3.0/5, which the old 100/70/30 => 5/4/2
        // mapping could never produce.
        $this->assertSame(3.0, $profile->fresh()->getAverageRating());
    }

    public function test_star_mirror_stays_in_step_with_the_percentage(): void
    {
        [$profile, $member] = $this->profileAndMember();

        $profile->rateByPercentage($member, 30);

        $rating = Rating::where('profile_id', $profile->id)->first();

        $this->assertSame(30, $rating->percentage);
        $this->assertSame(2, $rating->rating);
        $this->assertSame(1.5, $rating->stars);
    }

    public function test_a_rating_written_with_stars_only_gets_a_percentage(): void
    {
        [$profile, $member] = $this->profileAndMember();

        $rating = Rating::create([
            'profile_id' => $profile->id,
            'user_id' => $member->id,
            'rating' => 3,
        ]);

        $this->assertSame(60, $rating->fresh()->percentage);
    }

    public function test_out_of_range_percentages_are_refused(): void
    {
        [$profile, $member] = $this->profileAndMember();

        $this->assertSame(Profile::RATE_INVALID, $profile->rateByPercentage($member, 0));
        $this->assertSame(Profile::RATE_INVALID, $profile->rateByPercentage($member, 101));
        $this->assertSame(0, Rating::count());
    }

    public function test_rating_presets_come_from_the_admin_settings(): void
    {
        $this->assertSame(
            ['high' => 100, 'mid' => 70, 'low' => 30],
            RatingScale::options()
        );

        Setting::set(RatingScale::KEY_MID, 75);

        $this->assertSame(75, RatingScale::options()['mid']);
        $this->assertTrue(RatingScale::isOffered(75));
        $this->assertFalse(RatingScale::isOffered(70));
    }

    public function test_settings_are_clamped_to_the_valid_range(): void
    {
        Setting::set(RatingScale::KEY_HIGH, 500);
        Setting::set(RatingScale::KEY_LOW, -20);

        $options = RatingScale::options();

        $this->assertSame(100, $options['high']);
        $this->assertSame(1, $options['low']);
    }

    public function test_ratings_still_cannot_be_given_by_providers_or_to_yourself(): void
    {
        [$profile, $member] = $this->profileAndMember();
        $provider = User::factory()->create(['gender' => 'female']);

        $this->assertSame(Profile::RATE_NOT_LOGGED_IN, $profile->rateByPercentage(null, 70));
        $this->assertSame(Profile::RATE_NOT_MEMBER, $profile->rateByPercentage($provider, 70));

        // The own-profile guard sits behind the gender check, so it only comes
        // into play for an owner who would otherwise be allowed to rate.
        $ownProfile = Profile::factory()->create(['user_id' => $member->id]);
        $this->assertSame(Profile::RATE_OWN_PROFILE, $ownProfile->rateByPercentage($member, 70));

        $this->assertSame(0, Rating::count());
    }
}
