<?php

namespace Tests\Feature;

use App\Models\SubscriptionType;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\MemberSubscriptionTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Missing Stripe keys used to surface as a 500 on the checkout route, because
 * the client throws "$config must be a string or an array" when handed null.
 * That is a deployment problem and has to read as one.
 */
class MembershipCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function member(): User
    {
        return User::factory()->create([
            'gender' => 'male',
            'email_verified_at' => now(),
        ]);
    }

    private function plan(): SubscriptionType
    {
        $this->seed(CurrencySeeder::class);
        $this->seed(MemberSubscriptionTypeSeeder::class);

        return SubscriptionType::forMembers()->active()->firstOrFail();
    }

    public function test_checkout_without_stripe_keys_reports_instead_of_erroring(): void
    {
        config(['services.stripe.secret' => null]);

        $plan = $this->plan();

        $response = $this->actingAs($this->member())
            ->post(route('account.member.membership.checkout', $plan));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_a_provider_plan_cannot_be_bought_as_a_membership(): void
    {
        config(['services.stripe.secret' => null]);
        $this->seed(CurrencySeeder::class);

        $providerPlan = SubscriptionType::create([
            'name' => ['cs' => 'VIP', 'en' => 'VIP'],
            'slug' => 'vip-test',
            'audience' => 'profile',
            'price' => 100,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        $this->actingAs($this->member())
            ->post(route('account.member.membership.checkout', $providerPlan))
            ->assertNotFound();
    }

    public function test_an_inactive_plan_cannot_be_bought(): void
    {
        config(['services.stripe.secret' => null]);

        $plan = $this->plan();
        $plan->update(['is_active' => false]);

        $this->actingAs($this->member())
            ->post(route('account.member.membership.checkout', $plan))
            ->assertNotFound();
    }

    public function test_the_plans_page_lists_what_can_be_bought(): void
    {
        $this->plan();

        $response = $this->actingAs($this->member())
            ->get(route('account.member.membership.index'));

        $response->assertSuccessful();
        $response->assertViewHas('types', fn ($types) => $types->isNotEmpty());
    }

    public function test_success_does_not_claim_a_membership_that_was_never_paid(): void
    {
        config(['services.stripe.secret' => null]);

        $member = $this->member();

        // No session id, so nothing can be verified. The page may send the
        // customer somewhere sensible; what matters is that landing on this
        // URL grants nothing.
        $this->actingAs($member)
            ->get(route('account.member.membership.success'))
            ->assertRedirect();

        $this->assertFalse($member->fresh()->hasActiveMembership());
        $this->assertSame(0, \App\Models\MemberSubscription::count());
    }
}
