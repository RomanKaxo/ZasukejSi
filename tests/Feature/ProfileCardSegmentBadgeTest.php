<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Segment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileCardSegmentBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_card_renders_manual_segment_badge(): void
    {
        // Segment names are locale-translatable; pin the locale so this assertion
        // doesn't depend on the app's configured default locale (APP_LOCALE=en
        // in this repo's .env, used by the test suite).
        app()->setLocale('cs');

        $segment = Segment::factory()->create(['name' => ['cs' => 'Top lokalita', 'en' => 'Top location']]);
        $profile = Profile::factory()->for(User::factory())->create(['status' => 'approved', 'is_public' => true]);
        $profile->segments()->attach($segment->id);

        $html = (string) $this->blade(
            '<x-profile-card :profile="$profile" />',
            ['profile' => $profile->fresh()->load('segments')]
        );

        $this->assertStringContainsString('Top lokalita', $html);
    }

    public function test_profile_card_does_not_render_a_segment_badge_when_none_assigned(): void
    {
        $profile = Profile::factory()->for(User::factory())->create(['status' => 'approved', 'is_public' => true]);

        $html = (string) $this->blade(
            '<x-profile-card :profile="$profile" />',
            ['profile' => $profile->fresh()->load('segments')]
        );

        $this->assertStringNotContainsString('home-profile-card-segment-badge', $html);
    }
}
