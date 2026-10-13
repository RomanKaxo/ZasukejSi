<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Stripe\HttpClient\CurlClient;
use Tests\TestCase;

class CheckoutTruthfulnessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CurrencySeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        config(['services.stripe.secret' => null]);
    }

    private function provider(): User
    {
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);
        Profile::factory()->for($user)->approved()->public()->create();

        return $user->fresh();
    }

    private function plan(): SubscriptionType
    {
        return SubscriptionType::create(['name' => ['cs' => 'Audit VIP'], 'slug' => 'audit-vip', 'audience' => 'profile', 'price' => 500, 'price_czk' => 500, 'duration_days' => 30, 'is_active' => true]);
    }

    public function test_provider_granted_query_requires_a_real_running_manual_subscription(): void
    {
        $provider = $this->provider();
        $this->actingAs($provider)->get('/account/subscription/success?granted=1')->assertOk()
            ->assertViewHas('paid', false)->assertViewHas('granted', false)->assertViewHas('subscription', null);
        $subscription = Subscription::create(['profile_id' => $provider->profile->id, 'subscription_type_id' => $this->plan()->id, 'status' => 'active', 'starts_at' => now()->subDays(40), 'ends_at' => now()->subDays(10), 'metadata' => ['source' => 'manual', 'paid' => false]]);
        $this->get('/account/subscription/success?granted=1')->assertOk()->assertViewHas('granted', false);
        $subscription->update(['ends_at' => now()->addDays(20)]);
        $this->get('/account/subscription/success?granted=1')->assertOk()
            ->assertViewHas('paid', false)->assertViewHas('granted', true)
            ->assertSee(__('front.subscription.activated_without_payment'))
            ->assertDontSee(__('front.subscription.activation_pending'));
    }

    public function test_paid_session_stays_pending_until_a_running_subscription_exists(): void
    {
        $provider = $this->provider();
        config(['services.stripe.secret' => 'sk_test_MOCK_ONLY']);
        $gateway = $this->createMock(ClientInterface::class);
        $gateway->expects($this->exactly(3))->method('request')->willReturn([
            json_encode(['id' => 'cs_mock_paid', 'object' => 'checkout.session', 'payment_status' => 'paid', 'metadata' => ['profile_id' => $provider->profile->id]]), 200, [],
        ]);
        ApiRequestor::setHttpClient($gateway);
        try {
            $this->actingAs($provider)->get('/account/subscription/success?session_id=cs_mock_paid')->assertOk()
                ->assertViewHas('paid', true)->assertViewHas('granted', false)->assertViewHas('subscription', null);
            $subscription = Subscription::create(['profile_id' => $provider->profile->id, 'subscription_type_id' => $this->plan()->id, 'status' => 'active', 'starts_at' => now()->subDays(40), 'ends_at' => now()->subDays(10), 'metadata' => ['stripe_session_id' => 'cs_mock_paid']]);
            $this->get('/account/subscription/success?session_id=cs_mock_paid')->assertOk()->assertViewHas('subscription', null);
            $subscription->update(['ends_at' => now()->addDays(20)]);
            $this->get('/account/subscription/success?session_id=cs_mock_paid')->assertOk()
                ->assertViewHas('subscription', fn ($shown) => $shown?->id === $subscription->id);
        } finally {
            ApiRequestor::setHttpClient(CurlClient::instance());
        }
    }

    public function test_disabled_provider_gateway_with_retained_keys_never_makes_http_request(): void
    {
        $gateway = $this->createMock(ClientInterface::class);
        $gateway->expects($this->never())->method('request');
        ApiRequestor::setHttpClient($gateway);
        try {
            PaymentMethod::create(['code' => 'stripe', 'is_enabled' => false, 'settings' => ['secret_key' => 'sk_test_DISABLED_MOCK_ONLY']]);
            $this->actingAs($this->provider())->from('/account/subscription')->post(route('account.subscription.checkout', $this->plan()))
                ->assertRedirect('/account/subscription')->assertSessionHas('error', __('front.membership.payments_unavailable'));
            $this->assertDatabaseCount('subscriptions', 0);
        } finally {
            ApiRequestor::setHttpClient(CurlClient::instance());
        }
    }

    public function test_legacy_environment_gateway_without_a_registry_row_still_works_through_mock(): void
    {
        config(['services.stripe.secret' => 'sk_test_LEGACY_MOCK_ONLY']);
        $gateway = $this->createMock(ClientInterface::class);
        $gateway->expects($this->once())->method('request')->willReturn([
            json_encode(['id' => 'cs_mock_created', 'object' => 'checkout.session', 'url' => 'https://checkout.stripe.test/local-only']), 200, [],
        ]);
        ApiRequestor::setHttpClient($gateway);
        try {
            $this->actingAs($this->provider())->post(route('account.subscription.checkout', $this->plan()))
                ->assertRedirect('https://checkout.stripe.test/local-only');
            $this->assertDatabaseCount('subscriptions', 0);
        } finally {
            ApiRequestor::setHttpClient(CurlClient::instance());
        }
    }
}
