<?php

namespace Tests\Feature;

use App\Models\MemberSubscription;
use App\Models\SubscriptionType;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The green bar above the member pages.
 *
 * The design has two states: a date while there is time left, and a countdown
 * with a link to extend once the end is near (`phone 360 px-1`). We only had
 * the first, and it offered no way to renew.
 */
class PremiumBannerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    private function member(): User
    {
        return User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
    }

    private function giveMembership(User $user, int $daysLeft): void
    {
        $type = SubscriptionType::create([
            'name' => ['cs' => 'Premium', 'en' => 'Premium'],
            'slug' => 'premium-' . $user->id,
            'audience' => 'member',
            'price' => 100,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        MemberSubscription::create([
            'user_id' => $user->id,
            'subscription_type_id' => $type->id,
            'status' => MemberSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDays(30 - $daysLeft),
            'ends_at' => now()->addDays($daysLeft),
        ]);
    }

    public function test_a_member_without_a_membership_sees_no_bar(): void
    {
        $this->actingAs($this->member())
            ->get('/account/member/ratings')
            ->assertDontSee('E6FEE8', false);
    }

    public function test_the_countdown_and_the_link_show_when_the_end_is_near(): void
    {
        $member = $this->member();
        $this->giveMembership($member, 5);

        $response = $this->actingAs($member)->get('/account/member/ratings');

        $response->assertSee('Máte aktivní členství Premium už jen 5 dní', false);
        $response->assertSee(__('front.membership.extend_link'), false);
        $response->assertSee(route('account.member.membership.index'), false);
    }

    public function test_a_date_is_shown_while_there_is_time_left(): void
    {
        $member = $this->member();
        $this->giveMembership($member, 25);

        $response = $this->actingAs($member)->get('/account/member/ratings');

        $response->assertDontSee('už jen', false);
        $response->assertSee('Vaše Premium členství platí do', false);
        // Renewing early has to be possible in both states.
        $response->assertSee(route('account.member.membership.index'), false);
    }

    /** The heading printed „Vítejte,," — the comma was in the template too. */
    public function test_the_welcome_heading_has_one_comma(): void
    {
        $member = $this->member();

        $html = $this->actingAs($member)->get('/account/member/ratings')->getContent();

        $this->assertStringNotContainsString('Vítejte,,', $html);
        $this->assertStringContainsString('Vítejte,', $html);
    }
}
