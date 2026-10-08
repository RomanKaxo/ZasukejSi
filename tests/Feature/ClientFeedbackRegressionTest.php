<?php

namespace Tests\Feature;

use App\Livewire\FavoriteButton;
use App\Livewire\PhotosManager;
use App\Livewire\ProfileForm;
use App\Livewire\ServicesManager;
use App\Models\Profile;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ClientFeedbackRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CurrencySeeder::class);
    }

    private function provider(): User
    {
        $user = User::factory()->create(['gender' => 'female']);
        Profile::factory()->for($user)->approved()->public()->create([
            'id' => 40,
            'display_name' => ['cs' => 'Tamara', 'en' => 'Tamara'],
            'country_code' => 'CZ', 'city' => null, 'age' => 25,
            'content' => [], 'local_prices' => null, 'global_prices' => null,
            'availability_hours' => ['always_online' => false, 'schedule' => [
                'monday' => ['from' => '09:00', 'to' => '16:30'],
            ]],
        ]);

        return $user;
    }

    public function test_owner_can_preview_unpublished_profile_but_other_visitors_cannot(): void
    {
        $owner = $this->provider();
        $owner->profile->update(['status' => 'pending', 'is_public' => false]);
        $url = route('profiles.show', $owner->profile);

        $this->get($url)->assertNotFound();
        $this->actingAs(User::factory()->create(['gender' => 'male']))->get($url)->assertNotFound();
        $this->actingAs($owner)->get($url)->assertOk()
            ->assertSee(__('front.profiles.detail_page.owner_preview'));
        $this->assertDatabaseCount('profile_views', 0);
    }

    public function test_nickname_field_edits_public_name_and_email_is_visible(): void
    {
        $owner = $this->provider();
        $this->actingAs($owner)->get(route('account.dashboard'))->assertOk()
            ->assertSee('wire:model="display_name"', false)
            ->assertSee('value="'.$owner->email.'"', false);

        $component = Livewire::actingAs($owner)->test(ProfileForm::class)
            ->set('display_name', 'Tamara Updated')->call('save')->assertHasNoErrors();

        // A leading BOM made Livewire attach the component ID to the success
        // banner's image instead of the root, breaking subsequent edits.
        $this->assertMatchesRegularExpression('/^<div\b[^>]*wire:id="/', ltrim($component->html()));

        $this->assertSame('Tamara Updated', $owner->profile->fresh()->display_name);
        $this->get(route('profiles.show', $owner->profile))->assertSee('Tamara Updated');
    }

    public function test_selected_main_photo_leads_public_gallery_and_favorites_after_reload(): void
    {
        Storage::fake('public');
        Queue::fake();
        $owner = $this->provider();
        $profile = $owner->profile;
        $first = $profile->addMedia(UploadedFile::fake()->image('first.jpg'))
            ->withCustomProperties(['is_main' => true])->toMediaCollection('profile-images');
        $chosen = $profile->addMedia(UploadedFile::fake()->image('chosen.jpg'))
            ->toMediaCollection('profile-images');

        Livewire::actingAs($owner)->test(PhotosManager::class)->call('setAsMainPhoto', $chosen->id);

        $this->assertSame([$chosen->id, $first->id], $profile->fresh()->getAllImages()->modelKeys());
        $this->assertSame($chosen->getUrl(), $profile->fresh()->getFirstImageUrl());
        // An invalid or another owner's media ID must not clear the main photo.
        Livewire::actingAs($owner)->test(PhotosManager::class)->call('setAsMainPhoto', 999999);
        $this->assertTrue($chosen->fresh()->getCustomProperty('is_main'));

        // Distinct pivot/profile IDs catch identity confusion in joined results.
        $other = Profile::factory()->for(User::factory()->create(['gender' => 'female']))
            ->approved()->public()->create(['display_name' => 'Estella']);
        $member = User::factory()->create(['gender' => 'male']);
        Livewire::actingAs($member)->test(FavoriteButton::class, ['profile' => $profile])
            ->call('toggleFavorite')->assertSet('isFavorited', true);

        $this->assertDatabaseHas('profile_favorites', ['user_id' => $member->id, 'profile_id' => $profile->id]);
        $this->assertFalse($member->hasFavorited($other));
        for ($reload = 0; $reload < 2; $reload++) {
            $this->actingAs($member)->get(route('account.member.favorites'))->assertOk()
                ->assertSee('Tamara')->assertDontSee('Estella')
                ->assertSee($chosen->getUrl('thumb'), false)
                ->assertSee(route('profiles.show', $profile), false);
        }
    }

    public function test_services_and_languages_autosave_without_saving_draft_availability(): void
    {
        $owner = $this->provider();
        $original = $owner->profile->availability_hours;
        $service = Service::create(['name' => ['cs' => 'Masáž', 'en' => 'Massage'], 'is_active' => true]);

        Livewire::actingAs($owner)->test(ServicesManager::class)
            ->set('schedule.monday.from', '12:30')
            ->call('toggleService', $service->id)->assertDispatched('services-saved')
            ->call('toggleLanguage', 'Angličtina')->assertDispatched('services-saved');

        $this->assertTrue($owner->profile->fresh()->services->contains($service));
        $this->assertSame('Angličtina', $owner->profile->fresh()->languages);
        $this->assertSame($original, $owner->profile->fresh()->availability_hours);
    }

    public function test_availability_saves_only_on_confirmation_and_acknowledges_success(): void
    {
        $owner = $this->provider();
        Livewire::actingAs($owner)->test(ServicesManager::class)
            ->set('schedule.monday.from', '12:30')
            ->call('saveAvailability')->assertHasNoErrors()->assertDispatched('availability-saved');

        $this->assertSame('12:30', $owner->profile->fresh()->availability_hours['schedule']['monday']['from']);
    }

    public function test_invalid_availability_keeps_database_unchanged_without_success_acknowledgement(): void
    {
        $owner = $this->provider();
        $original = $owner->profile->availability_hours;
        Livewire::actingAs($owner)->test(ServicesManager::class)
            ->set('schedule.monday.from', 'invalid')->call('saveAvailability')
            ->assertHasErrors('schedule.monday.from')->assertNotDispatched('availability-saved');
        $this->assertSame($original, $owner->profile->fresh()->availability_hours);
    }

    public function test_cleared_day_stays_closed_when_the_form_is_reopened(): void
    {
        $owner = $this->provider();
        Livewire::actingAs($owner)->test(ServicesManager::class)
            ->set('schedule.monday.from', '')->set('schedule.monday.to', '')
            ->call('saveAvailability')->assertHasNoErrors();

        Livewire::actingAs($owner)->test(ServicesManager::class)
            ->assertSet('schedule.monday.from', '')->assertSet('schedule.monday.to', '');
    }
}
