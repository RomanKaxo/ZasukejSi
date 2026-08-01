<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-end smoke coverage for the main authenticated areas, plus the gender
 * authorization introduced on the account/member route groups.
 */
class SmokeRoutesTest extends TestCase
{
    use RefreshDatabase;

    private function member(): User
    {
        return User::factory()->create([
            'gender' => 'male',
            'email_verified_at' => now(),
        ]);
    }

    private function provider(): User
    {
        $user = User::factory()->create([
            'gender' => 'female',
            'email_verified_at' => now(),
        ]);

        Profile::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'is_public' => true,
        ]);

        return $user;
    }

    public function test_guest_pages_render(): void
    {
        $this->get('/')->assertOk();
        $this->get('/countries')->assertOk();
        $this->get('/api/profiles')->assertOk();
    }

    public function test_public_profile_detail_renders(): void
    {
        $provider = $this->provider();

        $this->get('/profiles/' . $provider->profile->id)->assertOk();
    }

    public function test_member_can_reach_every_member_page(): void
    {
        $member = $this->member();

        foreach ([
            '/account/member',
            '/account/member/ratings',
            '/account/member/favorites',
            '/account/member/girls-of-month',
            '/account/member/archive',
            '/account/member/reported',
            '/account/member/password',
            '/messages',
            '/notifications/archived',
        ] as $uri) {
            $this->actingAs($member)->get($uri)->assertOk();
        }
    }

    public function test_provider_can_reach_every_provider_page(): void
    {
        $provider = $this->provider();

        foreach ([
            '/account',
            '/account/profile',
            '/account/password',
            '/account/photos',
            '/account/services',
            '/account/statistics',
            '/account/reviews',
        ] as $uri) {
            $this->actingAs($provider)->get($uri)->assertOk();
        }
    }

    public function test_provider_is_redirected_away_from_member_area(): void
    {
        $provider = $this->provider();

        $this->actingAs($provider)
            ->get('/account/member/ratings')
            ->assertRedirect(route('account.dashboard'));
    }

    public function test_member_is_redirected_away_from_provider_pages(): void
    {
        $member = $this->member();

        $this->actingAs($member)
            ->get('/account/photos')
            ->assertRedirect(route('account.member.dashboard'));
    }

    public function test_member_password_form_renders(): void
    {
        // Regression: this route was registered but its view did not exist,
        // so the page returned a 500.
        $this->actingAs($this->member())
            ->get(route('account.member.password.edit'))
            ->assertOk();
    }
}
