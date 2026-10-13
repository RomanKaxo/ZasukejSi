<?php

namespace Tests\Feature;

use App\Livewire\CountryProfiles;
use App\Livewire\ProfileList;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class PublicBrowsingRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function profile(array $overrides = []): Profile
    {
        return Profile::factory()->create(array_merge([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'status' => 'approved',
            'is_public' => true,
            'verified_at' => now(),
            'content' => [],
        ], $overrides));
    }

    public function test_oldest_sort_takes_priority_on_homepage(): void
    {
        $old = $this->profile(['created_at' => now()->subDays(10)]);
        $this->profile(['created_at' => now()]);
        $list = Livewire::test(ProfileList::class)->set('sortNew', 'asc')->instance();
        $this->assertSame($old->id, $list->profiles->first()->id);
    }

    public function test_oldest_sort_takes_priority_on_countries(): void
    {
        $old = $this->profile(['created_at' => now()->subDays(10)]);
        $this->profile(['created_at' => now()]);
        $list = Livewire::test(CountryProfiles::class)->set('sortNew', 'asc')->instance();
        $this->assertSame($old->id, $list->profiles->first()->id);
    }

    public function test_recommendation_can_promote_an_older_higher_rated_profile(): void
    {
        $old = $this->profile(['created_at' => now()->subDays(10)]);
        $new = $this->profile(['created_at' => now()]);
        $old->rateByPercentage(User::factory()->create(['gender' => 'male']), 100);
        $new->rateByPercentage(User::factory()->create(['gender' => 'male']), 20);
        $list = Livewire::test(ProfileList::class)->set('sortRecommendation', 'desc')->instance();
        $this->assertSame($old->id, $list->profiles->first()->id);
    }

    public function test_home_and_country_video_filters_use_uploaded_video_collection(): void
    {
        $profile = $this->profile();
        $legacy = $this->profile();
        $photo = $this->profile();
        foreach ([[$profile, 'profile-video', 'video/mp4'], [$legacy, 'profile-images', 'video/mp4'], [$photo, 'profile-images', 'image/jpeg']] as [$owner, $collection, $mime]) {
            Media::create([
                'model_type' => Profile::class, 'model_id' => $owner->id,
                'collection_name' => $collection, 'name' => 'Audit media',
                'file_name' => 'audit-media', 'mime_type' => $mime,
                'disk' => 'public', 'size' => 10, 'manipulations' => [],
                'custom_properties' => [], 'generated_conversions' => [], 'responsive_images' => [],
            ]);
        }
        foreach ([ProfileList::class, CountryProfiles::class] as $class) {
            $list = Livewire::test($class)->set('hasVideo', true)->instance();
            $this->assertContains($profile->id, $list->profiles->pluck('id')->all());
            $this->assertContains($legacy->id, $list->profiles->pluck('id')->all());
            $this->assertNotContains($photo->id, $list->profiles->pluck('id')->all());
        }
    }

    public function test_guest_profile_rating_number_is_hidden(): void
    {
        $profile = $this->profile();
        $profile->rateByPercentage(User::factory()->create(['gender' => 'male']), 80);
        $response = $this->get('/profiles/'.$profile->id)->assertSuccessful();
        preg_match('/class="vip-profile-rating-icons">(.*?)<\/span>\s*<\/div>/s', $response->getContent(), $matches);
        $this->assertNotEmpty($matches);
        $this->assertStringNotContainsString('4.0', strip_tags($matches[1]));
    }

    public function test_public_api_rejects_invalid_page_size(): void
    {
        $this->profile();
        foreach (['-1', 'foo', '0', '101'] as $size) {
            $this->getJson('/api/profiles?per_page='.$size)
                ->assertStatus(422)
                ->assertJsonValidationErrors('per_page');
        }
    }

    public function test_a_free_member_cannot_read_the_profile_rating_number(): void
    {
        $profile = $this->profile();
        $profile->rateByPercentage(User::factory()->create(['gender' => 'male']), 80);
        $response = $this->actingAs(User::factory()->create(['gender' => 'male']))
            ->get('/profiles/'.$profile->id)->assertSuccessful();
        preg_match('/class="vip-profile-rating-icons">(.*?)<\/span>\s*<\/div>/s', $response->getContent(), $matches);
        $this->assertNotEmpty($matches);
        $this->assertStringNotContainsString('4.0', strip_tags($matches[1]));
    }

    public function test_provider_keeps_access_to_real_profile_ratings(): void
    {
        $profile = $this->profile();
        $profile->rateByPercentage(User::factory()->create(['gender' => 'male']), 80);
        $this->actingAs($profile->user)->get('/profiles/'.$profile->id)
            ->assertSuccessful()->assertSee('<span>4.0</span>', false);
    }

    public function test_public_api_does_not_expose_private_fields_or_rating_values(): void
    {
        $profile = $this->profile(['contacts' => ['private_note' => 'private-marker']]);
        $profile->rateByPercentage(User::factory()->create(['gender' => 'male']), 80);
        $this->getJson('/api/profiles?verified=true')->assertSuccessful()
            ->assertJsonPath('data.0.id', $profile->id)
            ->assertJsonMissingPath('data.0.contacts')
            ->assertJsonMissingPath('data.0.user')
            ->assertJsonMissingPath('data.0.email')
            ->assertJsonMissingPath('data.0.phone')
            ->assertJsonMissingPath('data.0.rating')
            ->assertJsonMissingPath('data.0.percentage')
            ->assertDontSee('private-marker');
    }

    public function test_selected_region_and_country_survive_a_url_reload(): void
    {
        foreach (['CZ', 'SK'] as $code) {
            DB::table('cities')->insert([
                'name' => 'Audit Town', 'name_ascii' => 'Audit Town',
                'country_code' => $code, 'admin_name' => 'Audit Region',
            ]);
        }
        $czech = $this->profile([
            'country_code' => 'CZ', 'city' => 'Audit Town',
            'display_name' => ['cs' => 'Czech reload profile', 'en' => 'Czech reload profile'],
        ]);
        $this->profile([
            'country_code' => 'SK', 'city' => 'Audit Town',
            'display_name' => ['cs' => 'Slovak reload profile', 'en' => 'Slovak reload profile'],
        ]);
        $selected = Livewire::test(CountryProfiles::class)
            ->call('selectRegion', 'CZ', 'Audit Region')
            ->assertSet('region', 'Audit Region')
            ->assertSet('selectedCountryCode', 'CZ')->instance();
        $this->assertSame([$czech->id], $selected->profiles->pluck('id')->all());
        $this->get('/countries?country_code=CZ&region=Audit%20Region')
            ->assertSuccessful()->assertSee('Czech reload profile')
            ->assertDontSee('Slovak reload profile');
    }

    public function test_country_button_filters_profiles_when_it_expands(): void
    {
        $czech = $this->profile(['country_code' => 'CZ']);
        $this->profile(['country_code' => 'SK']);
        $list = Livewire::test(CountryProfiles::class)
            ->call('toggleCountryExpansion', 'CZ')
            ->assertSet('selectedCountryCode', 'CZ')->instance();
        $this->assertSame([$czech->id], $list->profiles->pluck('id')->all());
    }

    public function test_card_detail_links_can_be_disabled_for_archive_rows(): void
    {
        $profile = $this->profile();
        $url = route('profiles.show', $profile);
        $linked = view('components.profile-card', ['profile' => $profile])->render();
        $this->assertStringContainsString('home-profile-card-name-link', $linked);
        $this->assertStringContainsString('href="'.$url.'"', $linked);
        $unlinked = view('components.profile-card', ['profile' => $profile, 'showDetailLink' => false])->render();
        $this->assertStringNotContainsString('href="'.$url.'"', $unlinked);
        $this->assertStringNotContainsString('cursor-pointer', $unlinked);
    }

    public function test_explicit_empty_card_images_override_does_not_fall_back_to_media(): void
    {
        $profile = $this->profile();
        Media::create([
            'model_type' => Profile::class, 'model_id' => $profile->id,
            'collection_name' => 'profile-images', 'name' => 'Audit photo',
            'file_name' => 'audit-private-photo.jpg', 'mime_type' => 'image/jpeg',
            'disk' => 'public', 'size' => 10, 'manipulations' => [],
            'custom_properties' => [], 'generated_conversions' => [], 'responsive_images' => [],
        ]);
        $normal = view('components.profile-card', ['profile' => $profile])->render();
        $this->assertStringContainsString('audit-private-photo.jpg', $normal);
        $placeholder = view('components.profile-card', ['profile' => $profile, 'imagesOverride' => []])->render();
        $this->assertStringNotContainsString('audit-private-photo.jpg', $placeholder);
    }
}
