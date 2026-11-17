<?php

namespace Tests\Feature;

use App\Livewire\PhotosManager;
use App\Livewire\ProfileForm;
use App\Livewire\ProfileList;
use App\Livewire\ServicesManager;
use App\Models\Profile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Client report (dev, 11/2026): a provider edits photos, languages, services
 * and the about text in her account and none of it shows up "in the listing".
 *
 * The listing only shows approved AND public profiles. A provider's own
 * profile starts pending + hidden, so the edits were saved but she had no way
 * to see them and nothing told her why. These tests pin the account's
 * explanation, the owner preview, the edit round trip for a live profile, and
 * two lost updates the account form caused.
 */
class ProviderProfileVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // createProfile() notifies every admin, so the role has to exist.
        \Spatie\Permission\Models\Role::findOrCreate('admin', 'web');
    }

    private function provider(array $profile = []): User
    {
        $user = User::factory()->create([
            'gender' => 'female',
            'email_verified_at' => now(),
        ]);

        Profile::factory()->create(array_merge([
            'user_id' => $user->id,
            'display_name' => 'Angela Testovaci',
            'age' => 25,
            'city' => null,
            'country_code' => 'cz',
            'about' => 'Původní text',
            'content' => ['languages' => 'Čeština'],
            'status' => 'approved',
            'is_public' => true,
        ], $profile));

        return $user->refresh();
    }

    public function test_new_provider_profile_is_hidden_and_the_account_says_why(): void
    {
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);

        // A freshly registered provider is offered a way to create her profile.
        $this->actingAs($user)
            ->get('/account')
            ->assertOk()
            ->assertSee('data-create-profile', false)
            ->assertSee('wire:click="createProfile"', false);

        Livewire::actingAs($user)
            ->test(ProfileForm::class)
            ->set('display_name', 'Angela Nova')
            ->set('age', 24)
            ->set('country_code', 'cz')
            ->call('createProfile')
            ->assertHasNoErrors()
            ->assertRedirect(route('account.dashboard'));

        $profile = $user->fresh()->profile;
        $this->assertSame('pending', $profile->status);

        // Moderation stays: the fresh profile is not in the public listing.
        Livewire::test(ProfileList::class)->assertDontSee('Angela Nova');

        // …but the account tells her so, and links to her own preview.
        $this->actingAs($user->fresh())
            ->get('/account')
            ->assertOk()
            ->assertSee(__('front.account_visibility.pending'))
            ->assertSee(route('profiles.show', $profile->id), false);

        // The preview works for her and for nobody else.
        $this->get(route('profiles.show', $profile->id))->assertOk()->assertSee('Angela Nova');
        auth()->logout();
        $this->get(route('profiles.show', $profile->id))->assertNotFound();
    }

    public function test_a_male_member_cannot_create_a_provider_profile(): void
    {
        $member = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);

        Livewire::actingAs($member)
            ->test(ProfileForm::class)
            ->assertDontSeeHtml('data-create-profile')
            ->set('display_name', 'Nekdo')
            ->set('age', 30)
            ->set('country_code', 'cz')
            ->call('createProfile');

        $this->assertNull($member->fresh()->profile);
    }

    public function test_every_hidden_state_has_its_own_explanation(): void
    {
        $cases = [
            ['status' => 'rejected', 'is_public' => false, 'key' => 'rejected'],
            ['status' => 'approved', 'is_public' => false, 'key' => 'hidden'],
        ];

        foreach ($cases as $case) {
            $user = $this->provider(['status' => $case['status'], 'is_public' => $case['is_public']]);

            $this->actingAs($user)
                ->get('/account/photos')
                ->assertOk()
                ->assertSee(__('front.account_visibility.' . $case['key']))
                ->assertSee(__('front.account_visibility.preview'));
        }

        // A published profile is the normal state: no bar, the account pages
        // keep the Figma layout.
        $live = $this->provider(['status' => 'approved', 'is_public' => true]);
        $this->actingAs($live)
            ->get('/account/photos')
            ->assertOk()
            ->assertDontSee('data-profile-visibility="', false);
    }

    public function test_edits_of_a_live_profile_reach_the_listing_and_the_detail(): void
    {
        Storage::fake('public');
        $user = $this->provider();
        $profile = $user->profile;
        $service = Service::create(['name' => 'Masáž testovací', 'slug' => 'masaz-testovaci', 'is_active' => true, 'sort_order' => 1]);

        $profile->addMedia(UploadedFile::fake()->image('first.jpg'))
            ->withCustomProperties(['is_main' => true])
            ->toMediaCollection('profile-images');

        Livewire::actingAs($user->fresh())
            ->test(PhotosManager::class)
            ->set('images', [UploadedFile::fake()->image('nova.jpg')])
            ->call('uploadImages')
            ->assertHasNoErrors();

        $new = $profile->fresh()->getMedia('profile-images')->firstWhere('name', 'nova.jpg');
        $this->assertNotNull($new);

        Livewire::actingAs($user->fresh())->test(PhotosManager::class)->call('setAsMainPhoto', $new->id);
        Livewire::actingAs($user->fresh())->test(ServicesManager::class)
            ->call('toggleLanguage', 'Angličtina')
            ->call('toggleService', $service->id);
        Livewire::actingAs($user->fresh())->test(ProfileForm::class)
            ->set('about', 'Nový text o mně')
            ->call('save')
            ->assertHasNoErrors();

        $fresh = $profile->fresh();
        $this->assertSame($new->id, $fresh->getAllImages()->first()->id);

        // Listing card: the new main photo leads the slideshow.
        $html = Livewire::test(ProfileList::class)->assertSee('Angela Testovaci')->html();
        $this->assertMatchesRegularExpression("/data-image-urls='([^']*)'/", $html);
        preg_match("/data-image-urls='([^']*)'/", $html, $match);
        $urls = json_decode(html_entity_decode($match[1]), true);
        $this->assertStringContainsString('/media/' . $new->id . '/', $urls[0]);

        auth()->logout();
        $this->get(route('profiles.show', $profile->id) . '?locale=cs')
            ->assertOk()
            ->assertSee('Nový text o mně')
            ->assertSee('Masáž testovací')
            ->assertSee('Angličtina');
    }

    public function test_saving_the_profile_form_keeps_languages_and_hours_set_on_the_services_page(): void
    {
        $user = $this->provider();

        // The settings form is open (e.g. another tab) while she changes
        // languages and hours on the services page.
        $form = Livewire::actingAs($user)->test(ProfileForm::class);

        Livewire::actingAs($user->fresh())->test(ServicesManager::class)
            ->call('toggleLanguage', 'Angličtina')
            ->set('schedule.monday.from', '10:00')
            ->set('schedule.monday.to', '18:00')
            ->call('saveAvailability');

        $form->set('about', 'Upravený text')->call('save')->assertHasNoErrors();

        $profile = $user->profile->fresh();
        $this->assertSame('Upravený text', $profile->about);
        $this->assertSame('Čeština, Angličtina', $profile->languages);
        $this->assertSame(['from' => '10:00', 'to' => '18:00'], $profile->availability_hours['schedule']['monday'] ?? null);
    }

    public function test_saving_the_profile_form_does_not_undo_an_admin_hiding_the_profile(): void
    {
        $user = $this->provider();
        $form = Livewire::actingAs($user)->test(ProfileForm::class);

        // An admin hides the profile (report "block" action) meanwhile.
        $user->profile->update(['is_public' => false]);

        $form->set('about', 'Upravený text')->call('save')->assertHasNoErrors();

        $this->assertFalse($user->profile->fresh()->is_public);
    }

    public function test_approving_a_profile_publishes_it_into_the_listing(): void
    {
        $profile = $this->provider(['status' => 'pending', 'is_public' => false])->profile;

        $profile->update(['status' => 'approved', 'verified_at' => now()]);

        $this->assertTrue($profile->fresh()->is_public);
        $this->assertTrue(Profile::approved()->public()->whereKey($profile->id)->exists());
    }

    public function test_an_approved_profile_hidden_by_admin_stays_hidden_on_later_updates(): void
    {
        $profile = $this->provider(['status' => 'approved', 'is_public' => false])->profile;

        $profile->update(['about' => 'Nový text', 'status' => 'approved']);

        $this->assertFalse($profile->fresh()->is_public);
    }
}
