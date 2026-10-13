<?php

namespace Tests\Feature;

use App\Livewire\ProfileForm;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProfileView;
use App\Models\Rating;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileDashboardValuesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, CurrencySeeder::class]);
    }

    private function provider(): User
    {
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);
        Profile::factory()->for($user)->create(['country_code' => 'CZ', 'city' => 'Praha', 'display_name' => ['cs' => 'Audit Owner', 'en' => 'Audit Owner'], 'age' => 25]);
        City::firstOrCreate(['country_code' => 'CZ', 'name' => 'Praha'], ['name_ascii' => 'Praha']);

        return $user->fresh();
    }

    private function card(string $html, string $icon): string
    {
        $this->assertSame(1, preg_match('~<img[^>]*src="[^"]*/'.preg_quote($icon, '~').'\.svg"[^>]*>\s*<span[^>]*>([^<]*)</span>~', $html, $matches));

        return trim(html_entity_decode($matches[1]));
    }

    public function test_an_empty_profile_does_not_show_demo_statistics(): void
    {
        $html = $this->actingAs($this->provider())->get('/account')->assertOk()->getContent();
        $this->assertSame('0', $this->card($html, 'Eye'));
        $this->assertSame('0', $this->card($html, 'MessageCircleMore'));
        $this->assertStringNotContainsString('4.78/5', $this->card($html, 'ThumbsUp'));
    }

    public function test_dashboard_statistics_match_only_the_current_profile(): void
    {
        $owner = $this->provider();
        $profile = $owner->profile;
        $other = Profile::factory()->for(User::factory()->create(['gender' => 'female']))->create();
        foreach (['impression', 'click', 'click'] as $type) {
            ProfileView::create(['profile_id' => $profile->id, 'type' => $type, 'viewed_date' => now()->toDateString()]);
        }
        ProfileView::create(['profile_id' => $other->id, 'type' => 'click', 'viewed_date' => now()->toDateString()]);
        Rating::create(['profile_id' => $profile->id, 'user_id' => User::factory()->create()->id, 'rating' => 5]);
        Rating::create(['profile_id' => $profile->id, 'user_id' => User::factory()->create()->id, 'rating' => 4]);
        Rating::create(['profile_id' => $other->id, 'user_id' => User::factory()->create()->id, 'rating' => 1]);
        $html = $this->actingAs($owner)->get('/account')->assertOk()->getContent();
        $this->assertSame('3', $this->card($html, 'Eye'));
        $this->assertSame('2', $this->card($html, 'MessageCircleMore'));
        $this->assertSame($profile->getAverageRating().'/5', $this->card($html, 'ThumbsUp'));
    }

    public function test_saved_country_and_city_match_the_form_options_after_reload(): void
    {
        $owner = $this->provider();
        Livewire::actingAs($owner)->test(ProfileForm::class)
            ->assertSet('country_code', 'cz')->assertSet('city', 'Praha');
    }

    public function test_country_and_city_stay_selected_after_save_and_a_new_form_instance(): void
    {
        $owner = $this->provider();
        Livewire::actingAs($owner)->test(ProfileForm::class)
            ->set('country_code', 'cz')->set('city', 'Praha')->call('save')->assertHasNoErrors();
        $this->assertSame('CZ', $owner->profile->fresh()->country_code);
        Livewire::actingAs($owner)->test(ProfileForm::class)
            ->assertSet('country_code','cz')->assertSet('city','Praha');
    }
}
