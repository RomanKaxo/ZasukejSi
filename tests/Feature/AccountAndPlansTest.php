<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\MemberSubscriptionTypeSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SubscriptionTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The site splits into two account experiences: a woman manages a profile, a
 * man holds a membership. They share a layout but almost nothing else, and
 * each route is gated to one of them.
 */
class AccountAndPlansTest extends TestCase
{
    use RefreshDatabase;

    private function provider(): User
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);
        Profile::factory()->create(['user_id' => $user->id]);

        return $user->fresh();
    }

    private function member(): User
    {
        $this->seed(RoleSeeder::class);

        return User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
    }

    // --- the two account areas ------------------------------------------

    public static function providerPages(): array
    {
        return [
            'nastenka' => ['/account'],
            'fotografie' => ['/account/photos'],
            'sluzby' => ['/account/services'],
            'statistiky' => ['/account/statistics'],
            'recenze' => ['/account/reviews'],
            'predplatne' => ['/account/subscription'],
            'profil' => ['/account/profile'],
            'heslo' => ['/account/password'],
        ];
    }

    /**
     * @dataProvider providerPages
     */
    public function test_a_provider_can_open_her_pages(string $url): void
    {
        $this->actingAs($this->provider())->get($url)->assertSuccessful();
    }

    public static function memberPages(): array
    {
        return [
            'nastenka' => ['/account/member'],
            'hodnoceni' => ['/account/member/ratings'],
            'oblibene' => ['/account/member/favorites'],
            'divky-mesice' => ['/account/member/girls-of-month'],
            'archiv' => ['/account/member/archive'],
            'nahlasene' => ['/account/member/reported'],
            'clenstvi' => ['/account/member/membership'],
            'heslo' => ['/account/member/password'],
        ];
    }

    /**
     * @dataProvider memberPages
     */
    public function test_a_member_can_open_his_pages(string $url): void
    {
        $this->actingAs($this->member())->get($url)->assertSuccessful();
    }

    public function test_a_member_is_sent_to_his_own_dashboard(): void
    {
        // /account belongs to providers; a member landing there is redirected
        // rather than shown a profile screen he has no profile for.
        $this->actingAs($this->member())
            ->get('/account')
            ->assertRedirect(route('account.member.dashboard'));
    }

    public function test_a_provider_cannot_reach_the_member_area(): void
    {
        $this->actingAs($this->provider())
            ->get('/account/member/ratings')
            ->assertRedirect();
    }

    public function test_account_pages_carry_the_site_footer(): void
    {
        // The account layout extends the app layout, so the footer comes with
        // it. A page that renders without one means the layout chain broke.
        $response = $this->actingAs($this->provider())->get('/account');

        $response->assertSuccessful();
        $response->assertSee('</footer>', false);
    }

    // --- the public plans page ------------------------------------------

    private function seedPlans(): void
    {
        $this->seed(CurrencySeeder::class);
        $this->seed(SubscriptionTypeSeeder::class);
        $this->seed(MemberSubscriptionTypeSeeder::class);
    }

    private function vipPage(): Page
    {
        return Page::updateOrCreate(
            ['slug' => 'vip-premium'],
            [
                'title' => ['cs' => 'VIP a Premium', 'en' => 'VIP & Premium'],
                'type' => 'page',
                'content' => ['cs' => [], 'en' => []],
                'is_published' => true,
            ]
        );
    }

    public function test_the_vip_page_lists_both_audiences(): void
    {
        $this->seedPlans();
        $this->vipPage();

        app()->setLocale('cs');

        $response = $this->get('/vip-premium');

        $response->assertSuccessful();
        $response->assertSee(__('front.plans.for_women'));
        $response->assertSee(__('front.plans.for_men'));
    }

    public function test_a_guest_is_asked_to_register_rather_than_shown_a_dead_button(): void
    {
        $this->seedPlans();
        $this->vipPage();

        app()->setLocale('cs');

        $this->get('/vip-premium')->assertSee(__('front.plans.register_to_buy'));
    }

    /**
     * A signed-in visitor is shown only what they can buy.
     *
     * Both sets used to be listed to everyone, so half the page was plans the
     * reader could only be told „this one is for women" about.
     */
    public function test_a_member_sees_only_the_membership_plans(): void
    {
        $this->seedPlans();
        $this->vipPage();

        $response = $this->actingAs($this->member())->get('/vip-premium');

        $response->assertSuccessful();
        $response->assertSee(route('account.member.membership.index'), false);
        $response->assertSee(__('front.plans.for_men'));
        $response->assertDontSee(__('front.plans.for_women'));
    }

    public function test_a_provider_sees_only_her_own_subscriptions(): void
    {
        $this->seedPlans();
        $this->vipPage();

        $response = $this->actingAs($this->provider())->get('/vip-premium');

        $response->assertSuccessful();
        $response->assertSee(route('account.subscription.index'), false);
        $response->assertSee(__('front.plans.for_women'));
        $response->assertDontSee(__('front.plans.for_men'));
    }

    /** A guest has not said which side they are on, so both are offered. */
    public function test_a_guest_sees_both_sets(): void
    {
        $this->seedPlans();
        $this->vipPage();

        $response = $this->get('/vip-premium');

        $response->assertSuccessful();
        $response->assertSee(__('front.plans.for_women'));
        $response->assertSee(__('front.plans.for_men'));
    }
}
