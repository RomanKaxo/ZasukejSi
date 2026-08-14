<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The provider "Recenze" page used to be a "coming soon" placeholder behind a
 * dead href="#", even though the ratings were already in the database.
 */
class AccountReviewsTest extends TestCase
{
    use RefreshDatabase;

    private function provider(): User
    {
        $user = User::factory()->create([
            'gender' => 'female',
            'email_verified_at' => now(),
        ]);

        Profile::factory()->create(['user_id' => $user->id]);

        return $user->fresh();
    }

    public function test_page_lists_ratings_the_profile_received(): void
    {
        $provider = $this->provider();
        $rater = User::factory()->create(['gender' => 'male', 'name' => 'Petr Novák']);

        Rating::create([
            'profile_id' => $provider->profile->id,
            'user_id' => $rater->id,
            'rating' => 4,
        ]);

        $response = $this->actingAs($provider)->get(route('account.reviews'));

        $response->assertOk();
        $response->assertSee('Petr Novák');
        $response->assertViewHas('totalRatings', 1);
        $response->assertViewHas('averageRating', 4.0);
    }

    public function test_average_is_null_when_there_are_no_ratings(): void
    {
        $provider = $this->provider();

        $response = $this->actingAs($provider)->get(route('account.reviews'));

        $response->assertOk();
        $response->assertViewHas('averageRating', null);
        $response->assertViewHas('totalRatings', 0);
        // The tile stays; only the value falls back to the neutral dash.
        $response->assertSee(__('front.account.reviews.average'));
    }

    public function test_sidebar_links_to_the_reviews_page(): void
    {
        $provider = $this->provider();

        $response = $this->actingAs($provider)->get(route('account.reviews'));

        $response->assertOk();
        $response->assertSee(route('account.reviews'), false);
    }
}
