<?php
namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GirlsOfMonthTest extends TestCase
{
    use RefreshDatabase;

    public function test_top_ten_uses_current_month_ratings_without_the_removed_age_filter(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 16)->startOfDay());
        $this->seed(\Database\Seeders\CurrencySeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $member = User::factory()->create(['gender' => 'male']);
        $expected = [];
        for ($i = 0; $i < 12; $i++) {
            $profile = Profile::factory()->create(['status' => 'approved', 'is_public' => true, 'age' => 30]);
            Rating::factory()->create(['user_id' => $member->id, 'profile_id' => $profile->id, 'percentage' => 100 - $i]);
            if ($i < 10) $expected[] = $profile->id;
        }
        foreach ([['approved', true, now()->subMonth()], ['approved', true, now()->addMonth()], ['draft', true, now()], ['approved', false, now()]] as [$status, $public, $date]) {
            $profile = Profile::factory()->create(['status' => $status, 'is_public' => $public]);
            Rating::factory()->create(['user_id' => $member->id, 'profile_id' => $profile->id, 'percentage' => 100, 'created_at' => $date]);
        }
        $response = $this->actingAs($member)->get('/account/member/girls-of-month?age_range=18-20');
        $response->assertOk()->assertSee('TOP 10')->assertDontSee('name="age_range"', false)
            ->assertDontSee(__('front.account.member.girls_of_month_description'));
        $this->assertSame($expected, $response->viewData('profiles')->pluck('id')->all());
    }

    public function test_no_current_month_ratings_does_not_create_a_random_ranking(): void
    {
        $this->seed(\Database\Seeders\CurrencySeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $member = User::factory()->create(['gender' => 'male']);
        $profile = Profile::factory()->create(['status' => 'approved', 'is_public' => true]);
        Rating::factory()->create(['user_id' => $member->id, 'profile_id' => $profile->id, 'created_at' => now()->startOfMonth()->subDay()]);
        $response = $this->actingAs($member)->get('/account/member/girls-of-month');
        $response->assertOk();
        $this->assertCount(0, $response->viewData('profiles'));
    }
}
