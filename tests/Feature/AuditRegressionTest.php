<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Profile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the defects found during the project audit.
 * Each test names the bug it locks down.
 */
class AuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function member(): User
    {
        return User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
    }

    private function providerWithProfile(array $profileAttributes = []): User
    {
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);

        Profile::factory()->create(array_merge([
            'user_id' => $user->id,
            'status' => 'approved',
            'is_public' => true,
        ], $profileAttributes));

        return $user->refresh();
    }

    /**
     * Bug: archiving or deleting a GLOBAL notification mutated the single shared
     * row, hiding it for every user on the platform.
     */
    public function test_archiving_a_global_notification_only_affects_that_user(): void
    {
        $a = $this->member();
        $b = $this->member();

        $global = Notification::createGlobal('Announcement', 'Visible to all');

        $this->assertSame(1, Notification::activeForUser($a->id)->where('id', $global->id)->count());
        $this->assertSame(1, Notification::activeForUser($b->id)->where('id', $global->id)->count());

        $this->actingAs($a)->delete(route('notifications.delete', $global));

        // Hidden for A, still visible for B, and the row itself survives.
        $this->assertSame(0, Notification::activeForUser($a->id)->where('id', $global->id)->count());
        $this->assertSame(1, Notification::activeForUser($b->id)->where('id', $global->id)->count());
        $this->assertNotNull(Notification::find($global->id));
    }

    /**
     * Bug: weight/height/bust/languages were read as real columns, which do not
     * exist — the values live in the `content` JSON column, so every profile
     * silently rendered the same hardcoded fallbacks.
     */
    public function test_physical_attributes_resolve_from_the_content_column(): void
    {
        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'content' => [
                'weight_kg' => '62',
                'card_height_cm' => '172',
                'bust_size' => 'D',
                'languages' => 'Česky, Anglicky',
            ],
        ]);

        $this->assertSame(62, $profile->weight);
        $this->assertSame(172, $profile->height);
        $this->assertSame('D', $profile->bust_size);
        $this->assertSame('Česky, Anglicky', $profile->languages);
        $this->assertSame(137, $profile->weight_lbs);
        $this->assertSame('5\'8"', $profile->height_feet);
    }

    public function test_missing_physical_attributes_are_null_not_fabricated(): void
    {
        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'content' => ['weight_kg' => '', 'card_height_cm' => null],
        ]);

        $this->assertNull($profile->weight);
        $this->assertNull($profile->height);
        $this->assertNull($profile->weight_lbs);
        $this->assertNull($profile->height_feet);
    }

    /**
     * Bug: ProfileController::show() selected a column subset that omitted
     * availability_hours and contacts, so real data never reached the view.
     */
    public function test_profile_detail_page_loads_availability_hours(): void
    {
        $provider = $this->providerWithProfile([
            'availability_hours' => ['Monday' => '10:00-18:00'],
        ]);

        $response = $this->get('/profiles/' . $provider->profile->id);

        $response->assertOk();
        $loaded = $response->viewData('profile');
        $this->assertSame(['Monday' => '10:00-18:00'], $loaded->availability_hours);
    }

    /**
     * Bug: ProfileRating::rate() had no members-only check (unlike the member
     * ratings page) and neither component stopped self-rating.
     */
    public function test_only_members_can_rate_and_never_their_own_profile(): void
    {
        $provider = $this->providerWithProfile();
        $profile = $provider->profile;
        $member = $this->member();

        $this->assertSame(Profile::RATE_NOT_LOGGED_IN, $profile->rateBy(null, 5));
        $this->assertSame(Profile::RATE_NOT_MEMBER, $profile->rateBy($provider, 5));
        $this->assertSame(Profile::RATE_INVALID, $profile->rateBy($member, 9));
        $this->assertSame(Profile::RATE_OK, $profile->rateBy($member, 4));

        $this->assertSame(4, $profile->fresh()->getUserRating($member->id));

        // A provider rating their own profile must be refused.
        $selfRater = User::factory()->create(['gender' => 'male']);
        $ownProfile = Profile::factory()->create(['user_id' => $selfRater->id]);
        $this->assertSame(Profile::RATE_OWN_PROFILE, $ownProfile->rateBy($selfRater, 5));
    }

    /**
     * Bug: toggleService() passed an unvalidated id straight to attach(),
     * so an unknown id hit the foreign key constraint and returned a 500.
     */
    public function test_toggling_an_unknown_service_is_rejected_without_error(): void
    {
        $provider = $this->providerWithProfile();

        $component = new \App\Livewire\ServicesManager();
        $this->actingAs($provider);
        $component->mount();
        $component->toggleService(999999);

        $this->assertSame([], $component->selectedServices);
        $this->assertSame(0, $provider->profile->services()->count());
    }

    public function test_toggling_a_real_service_works(): void
    {
        $provider = $this->providerWithProfile();
        $service = Service::create(['name' => 'Test service', 'is_active' => true]);

        $component = new \App\Livewire\ServicesManager();
        $this->actingAs($provider);
        $component->mount();
        $component->toggleService($service->id);

        $this->assertContains($service->id, $component->selectedServices);
    }

    /**
     * Bug: the media route ignored the filename segment, so any name served the
     * same file and every asset had unlimited duplicate URLs.
     */
    public function test_media_route_rejects_a_mismatched_filename(): void
    {
        $provider = $this->providerWithProfile();
        $profile = $provider->profile;

        // Use a real PNG shipped with the app: the collection enforces image
        // mime types, and generating one here would need the GD extension.
        $source = public_path('images/models/model1.png');
        $this->assertFileExists($source);

        // Conversions run through the queue; faking it keeps them from
        // executing, since generating them needs the GD/Imagick extension and
        // they play no part in what this test asserts.
        \Illuminate\Support\Facades\Queue::fake();

        $profile->addMedia($source)
            ->preservingOriginal()
            ->usingFileName('photo.png')
            ->toMediaCollection('profile-images');

        $media = $profile->fresh()->getFirstMedia('profile-images');

        $this->get('/media/' . $media->id . '/photo.png')->assertOk();
        $this->get('/media/' . $media->id . '/anything-else.png')->assertNotFound();
    }

    /**
     * Bug: UpdateLastActivity set `timestamps = false` on the authenticated
     * model, which also removed created_at/updated_at from the date casts for
     * the rest of the request — views calling ->diffForHumans() then fataled.
     */
    public function test_last_activity_update_keeps_timestamps_castable(): void
    {
        $provider = $this->providerWithProfile();

        // Force the middleware to perform its write on the next request.
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $provider->id)
            ->update(['last_activity' => time() - 3600]);

        $this->actingAs($provider)->get('/account/profile')->assertOk();

        $this->assertInstanceOf(
            \Illuminate\Support\Carbon::class,
            User::find($provider->id)->updated_at
        );
    }
}
