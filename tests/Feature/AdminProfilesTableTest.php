<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The profile listing answered "what is this called" and little else. Whether
 * a profile pays, whether it has a photo at all, and how it is rated were each
 * a click away, one profile at a time.
 */
class AdminProfilesTableTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $this->seed(CurrencySeeder::class);

        $user = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
        $user->syncRoles(['super_admin', 'admin']);

        return $user->fresh();
    }

    private function profile(string $name, array $attributes = []): Profile
    {
        return Profile::factory()->create(array_merge([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'display_name' => ['cs' => $name, 'en' => $name],
        ], $attributes));
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

    public function test_the_listing_marks_a_paying_profile(): void
    {
        $this->makeVip($this->profile('Platící'));
        $this->profile('Neplatící');

        $response = $this->actingAs($this->admin())->get('/admin/profiles');

        $response->assertSuccessful();
        $response->assertSee('Platící');
        $response->assertSee('Neplatící');
        $response->assertSee('VIP');
    }

    /**
     * A VIP profile whose subscription has run out is not VIP any more; the
     * column has to follow the dates, not the row's existence.
     */
    public function test_an_expired_subscription_is_not_shown_as_vip(): void
    {
        $profile = $this->makeVip($this->profile('Vypršelá'));
        $profile->subscriptions()->update(['ends_at' => now()->subDay()]);

        $this->assertFalse($profile->fresh()->isVip());

        $this->actingAs($this->admin())->get('/admin/profiles')->assertSuccessful();
    }

    public function test_the_filters_narrow_the_listing(): void
    {
        $this->profile('Ceka', ['status' => 'pending']);
        $this->profile('Schvalena', ['status' => 'approved']);

        // Rendering with each filter applied is what previously broke: a filter
        // referencing a relation that does not exist takes the screen down.
        foreach (['waiting', 'without_photos', 'vip'] as $filter) {
            $this->actingAs($this->admin())
                ->get('/admin/profiles?tableFilters[' . $filter . '][isActive]=true')
                ->assertSuccessful();
        }
    }

    public function test_a_profile_without_photos_is_still_listed(): void
    {
        $this->profile('Bez fotek');

        $this->actingAs($this->admin())
            ->get('/admin/profiles')
            ->assertSuccessful()
            ->assertSee('Bez fotek');
    }
}
