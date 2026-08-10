<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Segment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileDetailSegmentBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_detail_page_shows_segment_badge(): void
    {
        // Segment names are locale-translatable; pin the locale so this assertion
        // doesn't depend on the app's configured default locale (APP_LOCALE=en
        // in this repo's .env, used by the test suite). See ProfileCardSegmentBadgeTest.
        app()->setLocale('cs');

        \App\Models\City::create([
            'name' => 'Praha', 'name_ascii' => 'Praha', 'country_code' => 'CZ', 'population' => 1300000,
        ]);
        $segment = Segment::factory()->create(['name' => ['cs' => 'Ověřená', 'en' => 'Verified segment']]);
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'is_public' => true,
            'city' => 'Praha',
            'country_code' => 'cz',
        ]);
        $profile->segments()->attach($segment->id);

        $response = $this->get(route('profiles.show', $profile));

        $response->assertOk();
        $response->assertSee('Ověřená');
    }
}
