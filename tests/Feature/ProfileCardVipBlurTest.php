<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * On the profile detail's sliders only VIP profiles are held back. The card
 * used to blur the whole `vip-detail` variant, so every girl in "top rated"
 * was obscured — and because the profile URL was tied to the same flag, none
 * of them was clickable either.
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

    private function render(Profile $profile): string
    {
        return (string) view('components.profile-card', [
            'profile' => $profile,
            'variant' => 'vip-detail',
        ])->render();
    }

    public function test_a_vip_profile_is_blurred_but_still_leads_to_its_profile(): void
    {
        $profile = $this->makeVip($this->profile('VIP dívka'));

        $html = $this->render($profile);

        // The blur is a teaser, not a barrier — the visitor has to be able to
        // reach the profile to find out what unlocking it is worth.
        $this->assertStringContainsString('blur-md', $html);
        $this->assertStringContainsString(route('profiles.show', $profile), $html);
        $this->assertStringNotContainsString('pointer-events-none;', $html);
    }

    public function test_a_profile_without_vip_is_shown_and_links_to_itself(): void
    {
        $profile = $this->profile('Běžná dívka');

        $html = $this->render($profile);

        $this->assertStringNotContainsString('blur-md', $html);
        $this->assertStringContainsString(route('profiles.show', $profile), $html);
    }

    public function test_the_ordinary_variant_never_blurs(): void
    {
        // Everywhere else — the homepage, the listings — a VIP profile is shown
        // like any other. Only the detail page's sliders hold them back.
        $profile = $this->makeVip($this->profile('VIP dívka'));

        $html = (string) view('components.profile-card', ['profile' => $profile])->render();

        $this->assertStringNotContainsString('blur-md', $html);
        $this->assertStringContainsString(route('profiles.show', $profile), $html);
    }
}
