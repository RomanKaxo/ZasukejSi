<?php

namespace Tests\Feature;

use App\Models\MemberSubscription;
use App\Models\Notification;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Nothing moved subscriptions through their lifecycle.
 *
 * `Subscription::expire()` had no caller and the
 * `notifications.*.expiring_soon_*` strings were translated in both locales but
 * never sent — so a lapsed subscription stayed `active` with a past `ends_at`
 * forever, the admin counted it as live, and nobody was warned.
 */
class SubscriptionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function plan(string $audience = SubscriptionType::AUDIENCE_PROFILE): SubscriptionType
    {
        return SubscriptionType::create([
            'name' => ['cs' => 'Plán', 'en' => 'Plan'],
            'slug' => 'plan-' . $audience . '-' . uniqid(),
            'audience' => $audience,
            'price' => 100,
            'duration_days' => 30,
            'is_active' => true,
        ]);
    }

    private function profileSubscription(array $attributes): Subscription
    {
        $profile = Profile::factory()->for(User::factory()->create(['gender' => 'female']))->create();

        return Subscription::create(array_merge([
            'profile_id' => $profile->id,
            'subscription_type_id' => $this->plan()->id,
            'starts_at' => now()->subMonth(),
            'status' => Subscription::STATUS_ACTIVE,
        ], $attributes));
    }

    private function membership(array $attributes): MemberSubscription
    {
        return MemberSubscription::create(array_merge([
            'user_id' => User::factory()->create(['gender' => 'male'])->id,
            'subscription_type_id' => $this->plan(SubscriptionType::AUDIENCE_MEMBER)->id,
            'starts_at' => now()->subMonth(),
            'status' => MemberSubscription::STATUS_ACTIVE,
        ], $attributes));
    }

    public function test_a_lapsed_profile_subscription_is_expired(): void
    {
        $subscription = $this->profileSubscription(['ends_at' => now()->subDay()]);

        Artisan::call('subscriptions:lifecycle');

        $this->assertSame(Subscription::STATUS_EXPIRED, $subscription->fresh()->status);
    }

    public function test_a_lapsed_membership_is_expired_and_locks_ratings_again(): void
    {
        $membership = $this->membership(['ends_at' => now()->subDay()]);
        $user = $membership->user;

        $this->assertFalse($user->fresh()->hasActiveMembership(), 'A past end date must not count as active.');

        Artisan::call('subscriptions:lifecycle');

        $this->assertSame(MemberSubscription::STATUS_EXPIRED, $membership->fresh()->status);
        $this->assertFalse($user->fresh()->canSeeRatings());
    }

    public function test_a_subscription_still_running_is_left_alone(): void
    {
        $subscription = $this->profileSubscription(['ends_at' => now()->addDays(20)]);

        Artisan::call('subscriptions:lifecycle');

        $this->assertSame(Subscription::STATUS_ACTIVE, $subscription->fresh()->status);
    }

    public function test_expiring_soon_sends_a_warning(): void
    {
        $membership = $this->membership(['ends_at' => now()->addDays(3)]);
        Notification::query()->delete(); // ignore the "membership created" one

        Artisan::call('subscriptions:lifecycle');

        $this->assertTrue(
            Notification::where('user_id', $membership->user_id)->exists(),
            'A membership expiring within the window should warn the member.'
        );
        $this->assertNotNull($membership->fresh()->expiring_notified_at);
    }

    /**
     * The command runs nightly, so the warning must not be re-sent every night
     * for the whole week before expiry.
     */
    public function test_the_warning_is_sent_only_once_per_period(): void
    {
        $membership = $this->membership(['ends_at' => now()->addDays(3)]);
        Notification::query()->delete();

        Artisan::call('subscriptions:lifecycle');
        Artisan::call('subscriptions:lifecycle');
        Artisan::call('subscriptions:lifecycle');

        $this->assertSame(1, Notification::where('user_id', $membership->user_id)->count());
    }

    public function test_renewing_makes_the_warning_due_again(): void
    {
        $membership = $this->membership(['ends_at' => now()->addDays(3)]);
        Artisan::call('subscriptions:lifecycle');
        $this->assertNotNull($membership->fresh()->expiring_notified_at);

        $membership->fresh()->renew(30);

        $this->assertNull($membership->fresh()->expiring_notified_at);
    }

    public function test_a_subscription_outside_the_window_is_not_warned(): void
    {
        $membership = $this->membership(['ends_at' => now()->addDays(20)]);
        Notification::query()->delete();

        Artisan::call('subscriptions:lifecycle');

        $this->assertSame(0, Notification::where('user_id', $membership->user_id)->count());
        $this->assertNull($membership->fresh()->expiring_notified_at);
    }

    public function test_dry_run_changes_nothing(): void
    {
        $subscription = $this->profileSubscription(['ends_at' => now()->subDay()]);

        Artisan::call('subscriptions:lifecycle', ['--dry-run' => true]);

        $this->assertSame(Subscription::STATUS_ACTIVE, $subscription->fresh()->status);
    }
}
