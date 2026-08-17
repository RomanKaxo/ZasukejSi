<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\User;
use App\Support\TopRatedLock;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Who the profile detail's sliders are held back from.
 *
 * The design and the brief disagreed: the design blurs every card in „Nejlépe
 * hodnocené dívky" and the caption says Premium unlocks them, while the brief
 * said only VIP profiles are hidden. It is a setting now, so both readings are
 * covered here.
 *
 * What never changes: the blur is a teaser, not a barrier. The card still
 * leads to the profile, and the badges stay sharp.
 */
class ProfileCardVipBlurTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    private function profile(string $name): Profile
    {
        return Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'display_name' => ['cs' => $name, 'en' => $name],
            'status' => 'approved',
            'is_public' => true,
        ]);
    }

    private function makeVip(Profile $profile): Profile
    {
        $type = SubscriptionType::create([
            'name' => ['cs' => 'VIP', 'en' => 'VIP'],
            'slug' => 'vip-' . $profile->id,
            'audience' => 'profile',
            'price' => 100,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        Subscription::create([
            'profile_id' => $profile->id,
            'subscription_type_id' => $type->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);

        return $profile->fresh();
    }

    /** A member whose Premium is running. */
    private function premiumMember(): User
    {
        $user = User::factory()->create(['gender' => 'male']);

        $type = SubscriptionType::create([
            'name' => ['cs' => 'Premium', 'en' => 'Premium'],
            'slug' => 'premium-' . $user->id,
            'audience' => 'member',
            'price' => 100,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        // Memberships are their own table; `subscriptions` belongs to profiles.
        \App\Models\MemberSubscription::create([
            'user_id' => $user->id,
            'subscription_type_id' => $type->id,
            'status' => \App\Models\MemberSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);

        return $user->fresh();
    }

    private function mode(string $mode): void
    {
        \App\Models\Setting::set(TopRatedLock::KEY, $mode);
    }

    private function render(Profile $profile): string
    {
        return (string) view('components.profile-card', [
            'profile' => $profile,
            'variant' => 'vip-detail',
        ])->render();
    }

    // --- výchozí: bránou je Premium diváka ------------------------------

    public function test_premium_is_the_default_mode(): void
    {
        $this->assertSame(TopRatedLock::MODE_PREMIUM, TopRatedLock::mode());
    }

    public function test_a_guest_sees_every_card_held_back(): void
    {
        $this->mode(TopRatedLock::MODE_PREMIUM);

        // Not VIP — under this mode that makes no difference.
        $this->assertStringContainsString('blur-md', $this->render($this->profile('Běžná dívka')));
        $this->assertStringContainsString('blur-md', $this->render($this->makeVip($this->profile('VIP dívka'))));
    }

    public function test_an_active_premium_member_sees_them_all(): void
    {
        $this->mode(TopRatedLock::MODE_PREMIUM);
        $this->actingAs($this->premiumMember());

        $this->assertStringNotContainsString('blur-md', $this->render($this->profile('Běžná dívka')));
        $this->assertStringNotContainsString('blur-md', $this->render($this->makeVip($this->profile('VIP dívka'))));
    }

    public function test_a_member_without_premium_does_not(): void
    {
        $this->mode(TopRatedLock::MODE_PREMIUM);
        $this->actingAs(User::factory()->create(['gender' => 'male']));

        $this->assertStringContainsString('blur-md', $this->render($this->profile('Běžná dívka')));
    }

    /** Providers advertise here; they are never locked out of their own market. */
    public function test_a_provider_is_never_locked_out(): void
    {
        $this->mode(TopRatedLock::MODE_PREMIUM);
        $this->actingAs(User::factory()->create(['gender' => 'female']));

        $this->assertStringNotContainsString('blur-md', $this->render($this->profile('Běžná dívka')));
    }

    // --- druhá poloha: skryté jsou jen VIP profily ----------------------

    public function test_vip_mode_holds_back_only_vip_profiles(): void
    {
        $this->mode(TopRatedLock::MODE_VIP);

        $this->assertStringContainsString('blur-md', $this->render($this->makeVip($this->profile('VIP dívka'))));
        $this->assertStringNotContainsString('blur-md', $this->render($this->profile('Běžná dívka')));
    }

    public function test_vip_mode_ignores_who_is_looking(): void
    {
        $this->mode(TopRatedLock::MODE_VIP);
        $this->actingAs($this->premiumMember());

        // Premium does not unlock anything in this mode — the tier of the
        // advertiser decides, not the membership of the viewer.
        $this->assertStringContainsString('blur-md', $this->render($this->makeVip($this->profile('VIP dívka'))));
    }

    // --- co platí v obou polohách ---------------------------------------

    public function test_a_held_back_card_still_leads_to_its_profile(): void
    {
        $this->mode(TopRatedLock::MODE_PREMIUM);
        $profile = $this->profile('Běžná dívka');

        $html = $this->render($profile);

        $this->assertStringContainsString('blur-md', $html);
        $this->assertStringContainsString(route('profiles.show', $profile), $html);
        $this->assertStringNotContainsString('pointer-events-none;', $html);
    }

    /** The design blurs the photo and the name; the VIP badge stays sharp. */
    public function test_the_badge_stays_sharp_behind_the_blur(): void
    {
        $this->mode(TopRatedLock::MODE_PREMIUM);

        $html = $this->render($this->makeVip($this->profile('VIP dívka')));

        $this->assertStringContainsString('blur-md', $html);
        $this->assertStringContainsString('home-profile-card-vip', $html);
    }

    public function test_the_ordinary_variant_never_blurs(): void
    {
        $this->mode(TopRatedLock::MODE_PREMIUM);

        // Everywhere else — the homepage, the listings — nothing is held back.
        // Only the detail page's sliders do this.
        $profile = $this->makeVip($this->profile('VIP dívka'));

        $html = (string) view('components.profile-card', ['profile' => $profile])->render();

        $this->assertStringNotContainsString('blur-md', $html);
        $this->assertStringContainsString(route('profiles.show', $profile), $html);
    }

    public function test_an_unknown_stored_mode_falls_back_to_the_default(): void
    {
        \App\Models\Setting::set(TopRatedLock::KEY, 'nesmysl');

        $this->assertSame(TopRatedLock::MODE_PREMIUM, TopRatedLock::mode());
    }
}
