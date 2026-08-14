<?php

namespace Tests\Feature;

use App\Http\Controllers\MembershipCheckoutController;
use App\Models\MemberSubscription;
use App\Models\Notification;
use App\Models\Profile;
use App\Models\SubscriptionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Paid membership for members.
 *
 * The design is built around it — locked ratings on every card, the
 * "Premium účet vám odemkne hodnocení" bar, and the "Vaše Premium členství
 * platí do …" banner — but nothing backed it: `subscriptions` is keyed on
 * `profile_id`, and only a female provider has a profile.
 */
class MemberSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private function memberPlan(int $days = 30): SubscriptionType
    {
        return SubscriptionType::create([
            'name' => ['cs' => 'Premium na měsíc', 'en' => 'Premium monthly'],
            'slug' => 'premium-test-' . $days,
            'audience' => SubscriptionType::AUDIENCE_MEMBER,
            'price' => 299,
            'duration_days' => $days,
            'is_active' => true,
        ]);
    }

    private function profilePlan(): SubscriptionType
    {
        return SubscriptionType::create([
            'name' => ['cs' => 'Elite', 'en' => 'Elite'],
            'slug' => 'elite-test',
            'audience' => SubscriptionType::AUDIENCE_PROFILE,
            'price' => 1000,
            'duration_days' => 30,
            'is_active' => true,
        ]);
    }

    private function member(): User
    {
        return User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
    }

    public function test_plans_are_separated_by_audience(): void
    {
        $this->memberPlan();
        $this->profilePlan();

        $this->assertSame(['premium-test-30'], SubscriptionType::forMembers()->pluck('slug')->all());
        $this->assertSame(['elite-test'], SubscriptionType::forProfiles()->pluck('slug')->all());
    }

    /**
     * Everything that existed before the `audience` column must keep behaving
     * as a profile plan.
     */
    public function test_existing_plans_default_to_the_profile_audience(): void
    {
        $type = SubscriptionType::create([
            'name' => ['cs' => 'Bez audience', 'en' => 'No audience'],
            'slug' => 'legacy-plan',
            'price' => 500,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        $this->assertSame(SubscriptionType::AUDIENCE_PROFILE, $type->fresh()->audience);
        $this->assertFalse($type->fresh()->isForMembers());
    }

    public function test_a_member_without_a_membership_cannot_see_ratings(): void
    {
        $member = $this->member();

        $this->assertFalse($member->hasActiveMembership());
        $this->assertFalse($member->canSeeRatings());
        $this->assertNull($member->membershipEndsAt());
    }

    public function test_an_active_membership_unlocks_ratings(): void
    {
        $member = $this->member();
        $plan = $this->memberPlan();

        MemberSubscription::create([
            'user_id' => $member->id,
            'subscription_type_id' => $plan->id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(29),
            'status' => MemberSubscription::STATUS_ACTIVE,
        ]);

        $member->refresh();

        $this->assertTrue($member->hasActiveMembership());
        $this->assertTrue($member->canSeeRatings());
        $this->assertNotNull($member->membershipEndsAt());
    }

    public function test_an_expired_membership_does_not_unlock_ratings(): void
    {
        $member = $this->member();
        $plan = $this->memberPlan();

        MemberSubscription::create([
            'user_id' => $member->id,
            'subscription_type_id' => $plan->id,
            'starts_at' => now()->subDays(40),
            'ends_at' => now()->subDay(),
            'status' => MemberSubscription::STATUS_ACTIVE,
        ]);

        $this->assertFalse($member->fresh()->hasActiveMembership());
    }

    /**
     * Providers run a listing, so ratings are part of their own product;
     * admins always see everything.
     */
    public function test_providers_and_admins_see_ratings_without_a_membership(): void
    {
        $provider = User::factory()->create(['gender' => 'female']);
        Profile::factory()->for($provider)->create();

        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create(['gender' => 'male']);
        $admin->assignRole('admin');

        $this->assertTrue($provider->canSeeRatings());
        $this->assertTrue($admin->canSeeRatings());
    }

    public function test_the_gate_refuses_guests(): void
    {
        $this->assertFalse(Gate::forUser(null)->allows('view-ratings'));
        $this->assertFalse(Gate::forUser($this->member())->allows('view-ratings'));
    }

    public function test_webhook_activates_a_membership_from_a_paid_session(): void
    {
        $member = $this->member();
        $plan = $this->memberPlan(30);

        MembershipCheckoutController::activateFromSession((object) [
            'id' => 'cs_test_1',
            'payment_intent' => 'pi_1',
            'amount_total' => 29900,
            'currency' => 'czk',
            'metadata' => (object) [
                'member_user_id' => $member->id,
                'subscription_type_id' => $plan->id,
            ],
        ]);

        $subscription = MemberSubscription::forUser($member->id)->first();

        $this->assertNotNull($subscription);
        $this->assertTrue($member->fresh()->hasActiveMembership());
        $this->assertSame('cs_test_1', $subscription->metadata['stripe_session_id']);
    }

    /**
     * Stripe retries webhooks; the same session must not create a second row.
     */
    public function test_replaying_the_same_session_is_idempotent(): void
    {
        $member = $this->member();
        $plan = $this->memberPlan();

        $session = (object) [
            'id' => 'cs_dup',
            'payment_intent' => 'pi_dup',
            'amount_total' => 29900,
            'currency' => 'czk',
            'metadata' => (object) [
                'member_user_id' => $member->id,
                'subscription_type_id' => $plan->id,
            ],
        ];

        MembershipCheckoutController::activateFromSession($session);
        MembershipCheckoutController::activateFromSession($session);

        $this->assertSame(1, MemberSubscription::forUser($member->id)->count());
    }

    /**
     * Buying again while still covered extends the end date instead of
     * stacking a second membership.
     */
    public function test_buying_again_extends_the_existing_membership(): void
    {
        $member = $this->member();
        $plan = $this->memberPlan(30);

        MemberSubscription::create([
            'user_id' => $member->id,
            'subscription_type_id' => $plan->id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(10),
            'status' => MemberSubscription::STATUS_ACTIVE,
        ]);

        MembershipCheckoutController::activateFromSession((object) [
            'id' => 'cs_renew',
            'payment_intent' => 'pi_renew',
            'amount_total' => 29900,
            'currency' => 'czk',
            'metadata' => (object) [
                'member_user_id' => $member->id,
                'subscription_type_id' => $plan->id,
            ],
        ]);

        $this->assertSame(1, MemberSubscription::forUser($member->id)->count());
        $this->assertEqualsWithDelta(
            40,
            now()->diffInDays(MemberSubscription::forUser($member->id)->first()->ends_at),
            1
        );
    }

    public function test_a_profile_plan_cannot_be_used_as_a_membership(): void
    {
        $member = $this->member();
        $profilePlan = $this->profilePlan();

        MembershipCheckoutController::activateFromSession((object) [
            'id' => 'cs_wrong',
            'payment_intent' => 'pi_wrong',
            'amount_total' => 100000,
            'currency' => 'czk',
            'metadata' => (object) [
                'member_user_id' => $member->id,
                'subscription_type_id' => $profilePlan->id,
            ],
        ]);

        $this->assertSame(0, MemberSubscription::count());
    }

    public function test_activation_notifies_the_member(): void
    {
        $member = $this->member();
        $plan = $this->memberPlan();

        MemberSubscription::create([
            'user_id' => $member->id,
            'subscription_type_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'status' => MemberSubscription::STATUS_ACTIVE,
        ]);

        $this->assertTrue(
            Notification::where('user_id', $member->id)->exists(),
            'Activating a membership should notify the member.'
        );
    }

    public function test_provider_checkout_only_offers_provider_plans(): void
    {
        $this->memberPlan();
        $this->profilePlan();

        $provider = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);
        Profile::factory()->for($provider)->create(['status' => 'approved', 'is_public' => true]);

        $response = $this->actingAs($provider)->get('/account/subscription');

        $response->assertOk();
        $response->assertViewHas('types', function ($types) {
            return $types->every(fn ($type) => $type->audience === SubscriptionType::AUDIENCE_PROFILE);
        });
    }
}
