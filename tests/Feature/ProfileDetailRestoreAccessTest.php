<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "Obnovit přístup" is password recovery — someone locked out of their
 * account. It pointed at the VIP & Premium marketing page instead.
 */
class ProfileDetailRestoreAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    private function profile(): Profile
    {
        return Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'display_name' => ['cs' => 'Jana', 'en' => 'Jana'],
            'status' => 'approved',
            'is_public' => true,
        ]);
    }

    public function test_a_guest_is_sent_to_the_forgot_password_form(): void
    {
        $response = $this->get(route('profiles.show', $this->profile()));

        $response->assertSuccessful();
        $response->assertSee(route('password.request'), false);
    }

    /**
     * The forgot-password form is behind the `guest` middleware, so sending a
     * signed-in visitor there would bounce them to the homepage. They get their
     * own password screen instead.
     */
    public function test_a_signed_in_member_is_sent_to_his_password_screen(): void
    {
        $member = User::factory()->create(['gender' => 'male']);

        $response = $this->actingAs($member)->get(route('profiles.show', $this->profile()));

        $response->assertSuccessful();
        $response->assertSee(route('account.member.password.edit'), false);
        $response->assertDontSee(route('password.request'), false);
    }

    public function test_a_signed_in_provider_is_sent_to_her_password_screen(): void
    {
        $provider = User::factory()->create(['gender' => 'female']);

        $response = $this->actingAs($provider)->get(route('profiles.show', $this->profile()));

        $response->assertSuccessful();
        $response->assertSee(route('account.password.edit'), false);
    }
}
