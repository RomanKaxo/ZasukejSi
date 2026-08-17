<?php

namespace Tests\Feature;

use App\Models\MemberSubscription;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\User;
use App\Support\OfflineCheckout;
use App\Support\SubscriptionRevenue;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Peníze na nástěnce.
 *
 * Dvě pravidla, na kterých čísla stojí: co Stripe skutečně strhl přebíjí
 * dnešní cenu tarifu, a předplatné přidělené bez platby se počítá mezi
 * aktivní, ale ne do tržeb.
 */
class SubscriptionRevenueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    private function plan(string $audience = 'profile', float $price = 1000): SubscriptionType
    {
        return SubscriptionType::create([
            'name' => ['cs' => 'Plán', 'en' => 'Plan'],
            'slug' => 'plan-' . uniqid(),
            'audience' => $audience,
            'price' => $price,
            'price_czk' => $price,
            'duration_days' => 30,
            'is_active' => true,
        ]);
    }

    private function profile(): Profile
    {
        return Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
        ]);
    }

    public function test_revenue_uses_the_plan_price_when_nothing_else_is_known(): void
    {
        Subscription::create([
            'profile_id' => $this->profile()->id,
            'subscription_type_id' => $this->plan(price: 1500)->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->addMonth(),
        ]);

        $this->assertSame(1500.0, app(SubscriptionRevenue::class)->total());
    }

    /** Cenu tarifu lze po zaplacení změnit; účtenka se tím měnit nesmí. */
    public function test_what_stripe_charged_wins_over_the_current_price(): void
    {
        $plan = $this->plan(price: 1500);

        Subscription::create([
            'profile_id' => $this->profile()->id,
            'subscription_type_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->addMonth(),
            'metadata' => ['amount_total' => 99000, 'currency' => 'czk'],
        ]);

        $plan->update(['price' => 9999, 'price_czk' => 9999]);

        $this->assertSame(990.0, app(SubscriptionRevenue::class)->total());
    }

    public function test_a_subscription_granted_without_payment_earns_nothing(): void
    {
        Subscription::create([
            'profile_id' => $this->profile()->id,
            'subscription_type_id' => $this->plan(price: 1500)->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'metadata' => OfflineCheckout::metadata(),
        ]);

        $revenue = app(SubscriptionRevenue::class);

        $this->assertSame(0.0, $revenue->total());
        // Ale běží — jinak by se počty nedaly srovnat.
        $this->assertSame(1, $revenue->activeProfileSubscriptions());
        $this->assertSame(1, $revenue->grantedWithoutPayment());
    }

    public function test_memberships_count_too(): void
    {
        MemberSubscription::create([
            'user_id' => User::factory()->create(['gender' => 'male'])->id,
            'subscription_type_id' => $this->plan('member', 500)->id,
            'status' => MemberSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);

        $revenue = app(SubscriptionRevenue::class);

        $this->assertSame(500.0, $revenue->total());
        $this->assertSame(1, $revenue->activeMemberships());
    }

    public function test_the_chart_covers_twelve_months_ending_now(): void
    {
        $monthly = app(SubscriptionRevenue::class)->monthly(12);

        $this->assertCount(12, $monthly);
        $this->assertArrayHasKey(now()->format('Y-m'), $monthly);
        $this->assertArrayHasKey(now()->subMonths(11)->format('Y-m'), $monthly);
    }

    public function test_a_purchase_lands_in_its_own_month(): void
    {
        Subscription::create([
            'profile_id' => $this->profile()->id,
            'subscription_type_id' => $this->plan(price: 700)->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->subMonths(2)->startOfMonth()->addDays(3),
            'ends_at' => now()->addMonth(),
        ]);

        $monthly = app(SubscriptionRevenue::class)->monthly(12);

        $this->assertSame(700.0, $monthly[now()->subMonths(2)->format('Y-m')]);
        $this->assertSame(0.0, $monthly[now()->format('Y-m')]);
    }

    public function test_expiring_soon_counts_both_products(): void
    {
        Subscription::create([
            'profile_id' => $this->profile()->id,
            'subscription_type_id' => $this->plan()->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->subDays(25),
            'ends_at' => now()->addDays(3),
        ]);

        MemberSubscription::create([
            'user_id' => User::factory()->create(['gender' => 'male'])->id,
            'subscription_type_id' => $this->plan('member')->id,
            'status' => MemberSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDays(25),
            'ends_at' => now()->addDays(5),
        ]);

        $this->assertSame(2, app(SubscriptionRevenue::class)->expiringWithin(7));
    }

    /** Meziroční srovnání potřebuje okno, ne odečet dvou součtů. */
    public function test_a_window_counts_only_what_falls_inside_it(): void
    {
        $plan = $this->plan(price: 400);

        foreach ([now()->subDays(5), now()->subMonths(14)] as $when) {
            Subscription::create([
                'profile_id' => $this->profile()->id,
                'subscription_type_id' => $plan->id,
                'status' => Subscription::STATUS_ACTIVE,
                'starts_at' => $when,
                'ends_at' => $when->copy()->addMonth(),
            ]);
        }

        $revenue = app(SubscriptionRevenue::class);

        $this->assertSame(800.0, $revenue->total());
        $this->assertSame(400.0, $revenue->between(now()->subYear(), now()->addSecond()));
        $this->assertSame(400.0, $revenue->between(now()->subYears(2), now()->subYear()));
    }

    public function test_purchases_are_counted_per_month(): void
    {
        $plan = $this->plan(price: 100);

        foreach ([now()->subDays(2), now()->subDays(3), now()->subMonths(2)] as $when) {
            Subscription::create([
                'profile_id' => $this->profile()->id,
                'subscription_type_id' => $plan->id,
                'status' => Subscription::STATUS_ACTIVE,
                'starts_at' => $when,
                'ends_at' => $when->copy()->addMonth(),
            ]);
        }

        $purchases = app(SubscriptionRevenue::class)->purchasesByMonth(12);

        $this->assertSame(2, $purchases[now()->format('Y-m')]);
        $this->assertSame(1, $purchases[now()->subMonths(2)->format('Y-m')]);
    }

    /** Přidělené bez platby se do tržeb nepočítá, ale koupené to je. */
    public function test_a_manual_grant_counts_as_a_purchase_but_not_as_revenue(): void
    {
        Subscription::create([
            'profile_id' => $this->profile()->id,
            'subscription_type_id' => $this->plan(price: 900)->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'metadata' => OfflineCheckout::metadata(),
        ]);

        $revenue = app(SubscriptionRevenue::class);

        $this->assertSame(0.0, $revenue->total());
        $this->assertSame(1, $revenue->purchasesByMonth(12)[now()->format('Y-m')]);
    }

    public function test_registrations_are_counted_per_month(): void
    {
        User::factory()->create(['created_at' => now()->subDays(2)]);
        User::factory()->create(['created_at' => now()->subDays(4)]);
        User::factory()->create(['created_at' => now()->subMonths(3)]);

        $registrations = app(SubscriptionRevenue::class)->registrationsByMonth(12);

        $this->assertSame(2, $registrations[now()->format('Y-m')]);
        $this->assertSame(1, $registrations[now()->subMonths(3)->format('Y-m')]);
    }

    public function test_all_three_series_share_the_same_months(): void
    {
        $revenue = app(SubscriptionRevenue::class);

        $months = array_keys($revenue->monthly(12));

        $this->assertSame($months, array_keys($revenue->purchasesByMonth(12)));
        $this->assertSame($months, array_keys($revenue->registrationsByMonth(12)));
    }

    public function test_something_ending_next_month_is_not_expiring_soon(): void
    {
        Subscription::create([
            'profile_id' => $this->profile()->id,
            'subscription_type_id' => $this->plan()->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addDays(20),
        ]);

        $this->assertSame(0, app(SubscriptionRevenue::class)->expiringWithin(7));
    }
}
