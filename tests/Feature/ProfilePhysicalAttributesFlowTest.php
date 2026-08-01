<?php

namespace Tests\Feature;

use App\Livewire\ProfileForm;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * End-to-end flow: a provider ("girl") fills in weight / height / bust / languages
 * in her account settings, and those exact values must appear on the public
 * profile detail page.
 *
 * These fields are stored inside the `profiles.content` JSON column by
 * App\Livewire\ProfileForm, and read back through accessors on the Profile model.
 */
class ProfilePhysicalAttributesFlowTest extends TestCase
{
    use RefreshDatabase;

    private function providerWithProfile(): User
    {
        // The form validates that the city exists for the selected country,
        // so give the test DB a real one to select.
        \App\Models\City::create([
            'name' => 'Praha',
            'name_ascii' => 'Praha',
            'country_code' => 'CZ',
            'population' => 1300000,
        ]);

        $user = User::factory()->create([
            'gender' => 'female',
            'email_verified_at' => now(),
        ]);

        Profile::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'is_public' => true,
            'display_name' => 'Test Girl',
            'age' => 25,
            'city' => 'Praha',
            'country_code' => 'cz',
            'content' => [],
        ]);

        return $user->refresh();
    }

    public function test_provider_settings_round_trip_to_public_profile(): void
    {
        $provider = $this->providerWithProfile();

        // 1. She fills the fields in her account settings and saves.
        Livewire::actingAs($provider)
            ->test(ProfileForm::class)
            ->set('display_name', 'Test Girl')
            ->set('age', 25)
            ->set('country_code', 'cz')
            ->set('weight_kg', '58')
            ->set('height_cm', '170')
            ->set('bust_size', 'C')
            ->set('languages', 'Česky, Anglicky')
            ->call('save')
            ->assertHasNoErrors();

        // 2. The values are persisted in the content column.
        $profile = $provider->fresh()->profile;
        $this->assertSame('58', $profile->content['weight_kg']);
        $this->assertSame('170', $profile->content['card_height_cm']);
        $this->assertSame('C', $profile->content['bust_size']);
        $this->assertSame('Česky, Anglicky', $profile->content['languages']);

        // 3. The accessors expose them, including the derived imperial units.
        $this->assertSame(58, $profile->weight);
        $this->assertSame(170, $profile->height);
        $this->assertSame(128, $profile->weight_lbs);
        $this->assertSame('5\'7"', $profile->height_feet);

        // 4. They actually render on the public profile detail page.
        $response = $this->get('/profiles/' . $profile->id);
        $response->assertOk();
        $response->assertSee('58');
        $response->assertSee('170');
        $response->assertSee('Česky, Anglicky');
    }

    public function test_reloading_the_settings_form_shows_the_saved_values(): void
    {
        $provider = $this->providerWithProfile();

        Livewire::actingAs($provider)
            ->test(ProfileForm::class)
            ->set('display_name', 'Test Girl')
            ->set('age', 25)
            ->set('country_code', 'cz')
            ->set('weight_kg', '58')
            ->set('height_cm', '170')
            ->set('languages', 'Česky')
            ->call('save')
            ->assertHasNoErrors();

        // Re-mounting the component must pre-fill what she previously saved.
        Livewire::actingAs($provider->fresh())
            ->test(ProfileForm::class)
            ->assertSet('weight_kg', '58')
            ->assertSet('height_cm', '170')
            ->assertSet('languages', 'Česky');
    }
}
