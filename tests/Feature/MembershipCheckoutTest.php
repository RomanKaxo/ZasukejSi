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

    /**
     * Without a gateway the order still goes through, so the flow can be
     * walked end to end — but nothing pretends money changed hands.
     */
    public function test_checkout_without_stripe_keys_completes_without_payment(): void
    {
        config(['services.stripe.secret' => null]);

        $plan = $this->plan();
        $member = $this->member();

        $response = $this->actingAs($member)
            ->post(route('account.member.membership.checkout', $plan));

        // Na informativní stránku, ne do nastavení účtu: kdo si právě koupil
        // nebo prodloužil členství, nemá skončit v nastavení hesla — a má se
        // dočíst, že se neplatilo.
        $response->assertRedirect(route('account.member.membership.success', ['granted' => 1]));

        $subscription = \App\Models\MemberSubscription::forUser($member->id)->active()->firstOrFail();

        $this->assertTrue(\App\Support\OfflineCheckout::wasManual($subscription->metadata));
        $this->assertTrue($member->fresh()->hasActiveMembership());
    }

    /** Turned off, the buyer is told rather than quietly given a membership. */
    public function test_the_operator_can_switch_the_no_gateway_path_off(): void
    {
        config(['services.stripe.secret' => null]);
        \App\Models\Setting::set(\App\Support\OfflineCheckout::KEY, '0');

        $plan = $this->plan();
        $member = $this->member();

        $response = $this->actingAs($member)
            ->post(route('account.member.membership.checkout', $plan));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertFalse($member->fresh()->hasActiveMembership());
    }

    /** A second purchase extends the running membership, it does not stack. */
    public function test_buying_again_without_a_gateway_extends_the_membership(): void
    {
        config(['services.stripe.secret' => null]);

        $plan = $this->plan();
        $member = $this->member();

        $this->actingAs($member)->post(route('account.member.membership.checkout', $plan));
        $first = \App\Models\MemberSubscription::forUser($member->id)->active()->firstOrFail();

        $this->actingAs($member)->post(route('account.member.membership.checkout', $plan));

        $this->assertSame(1, \App\Models\MemberSubscription::forUser($member->id)->count());
        $this->assertTrue(
            \App\Models\MemberSubscription::forUser($member->id)->active()->firstOrFail()->ends_at
                ->greaterThan($first->ends_at)
        );
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
            ->assertSuccessful()
            ->assertSee(__('front.membership.unverified_title'));

        $this->assertFalse($member->fresh()->hasActiveMembership());
        $this->assertSame(0, \App\Models\MemberSubscription::count());
    }
}
