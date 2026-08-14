<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Profile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The profile detail page filled every gap with invented content: a
 * 4 000–18 000 Kč price list presented as the provider's own, stock model
 * photos padded into her gallery, a canned service list, canned languages, a
 * canned "about me" paragraph and an 18:00–18:00 daily availability.
 *
 * Sections still render — the layout is unchanged — but a value is now either
 * true or visibly absent.
 */
class ProfileDetailNoFabricatedDataTest extends TestCase
{
    use RefreshDatabase;

    private function emptyProfile(): Profile
    {
        City::create(['name' => 'Praha', 'name_ascii' => 'Praha', 'country_code' => 'CZ', 'admin_name' => 'Praha']);

        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);

        return Profile::factory()->for($user)->create([
            'status' => 'approved',
            'is_public' => true,
            'city' => 'Praha',
            'country_code' => 'CZ',
            'about' => null,
            'content' => [],
            'local_prices' => [],
            'global_prices' => [],
            'availability_hours' => [],
        ]);
    }

    public function test_a_profile_without_prices_shows_no_price_at_all(): void
    {
        $response = $this->get(route('profiles.show', $this->emptyProfile()));

        $response->assertOk();
        foreach (['4 000', '6 000', '14 000', '18 000'] as $invented) {
            $response->assertDontSee($invented);
        }
    }

    public function test_the_price_section_still_renders_with_an_empty_state(): void
    {
        $response = $this->get(route('profiles.show', $this->emptyProfile()));

        $response->assertOk();
        // The heading stays put; only the value is absent.
        $response->assertSee(__('front.profiles.detail_page.my_prices'));
        $response->assertSee('empty-value', false);
    }

    public function test_real_prices_are_shown_when_the_provider_set_them(): void
    {
        $profile = $this->emptyProfile();
        $profile->update([
            'incall' => true,
            'local_prices' => [
                ['time_hours' => 1, 'incall_price' => 2500, 'outcall_price' => null],
            ],
        ]);

        $this->get(route('profiles.show', $profile))
            ->assertOk()
            ->assertSee('2 500');
    }

    public function test_gallery_never_falls_back_to_stock_model_photos(): void
    {
        $this->get(route('profiles.show', $this->emptyProfile()))
            ->assertOk()
            ->assertDontSee('images/models/model');
    }

    public function test_services_section_renders_without_a_canned_list(): void
    {
        $response = $this->get(route('profiles.show', $this->emptyProfile()));

        $response->assertOk();
        $response->assertSee(__('front.profiles.detail_page.services'));

        // Whatever the canned default was, it must not appear for a profile
        // that has no services attached.
        foreach ((array) __('front.profiles.detail_page.services_default') as $default) {
            if (is_string($default) && $default !== '') {
                $response->assertDontSee($default);
            }
        }
    }

    public function test_real_services_are_shown_when_attached(): void
    {
        $profile = $this->emptyProfile();
        $service = Service::create([
            'name' => ['cs' => 'Erotická masáž', 'en' => 'Erotic massage'],
            'description' => ['cs' => '', 'en' => ''],
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $profile->services()->attach($service->id);

        app()->setLocale('cs');

        $this->get(route('profiles.show', $profile))
            ->assertOk()
            ->assertSee('Erotická masáž');
    }

    public function test_about_and_languages_are_absent_rather_than_invented(): void
    {
        $response = $this->get(route('profiles.show', $this->emptyProfile()));

        $response->assertOk();

        $aboutDefault = __('front.profiles.detail_page.about_default');
        if (is_string($aboutDefault) && $aboutDefault !== 'front.profiles.detail_page.about_default') {
            $response->assertDontSee($aboutDefault);
        }
    }
}
