<?php

namespace Tests\Feature;

use App\Livewire\ProfileList;
use App\Models\Profile;
use App\Models\ProfileView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The homepage listing used to take the handful of featured profiles, repeat
 * them until they filled six pages, and hand the paginator a fabricated total
 * of perPage * 6. The result advertised 150 profiles built from five, and no
 * other profile on the site was reachable from the homepage at all.
 *
 * Featured profiles are now only an ordering hint.
 */
class HomepageRealDataTest extends TestCase
{
    use RefreshDatabase;

    private function publicProfile(array $attributes = []): Profile
    {
        return Profile::factory()->for(User::factory())->create(array_merge([
            'status' => 'approved',
            'is_public' => true,
        ], $attributes));
    }

    public function test_total_matches_the_real_number_of_public_profiles(): void
    {
        $this->publicProfile(['content' => ['is_showcase' => true]]);
        $this->publicProfile();
        $this->publicProfile();

        $paginator = Livewire::test(ProfileList::class)->instance()->profiles();

        $this->assertSame(3, $paginator->total());
        $this->assertSame(Profile::approved()->public()->count(), $paginator->total());
    }

    public function test_no_profile_is_repeated_within_a_page(): void
    {
        $this->publicProfile(['content' => ['is_showcase' => true]]);
        $this->publicProfile(['content' => ['is_showcase' => true]]);
        $this->publicProfile();

        $ids = collect(Livewire::test(ProfileList::class)->instance()->profiles()->items())->pluck('id');

        $this->assertSame($ids->count(), $ids->unique()->count());
    }

    public function test_non_featured_profiles_are_reachable_from_the_homepage(): void
    {
        $this->publicProfile(['content' => ['is_showcase' => true], 'display_name' => 'Featured One']);
        $this->publicProfile(['display_name' => 'Ordinary One']);

        Livewire::test(ProfileList::class)
            ->assertSee('Featured One')
            ->assertSee('Ordinary One');
    }

    public function test_featured_profiles_sort_first_while_unfiltered(): void
    {
        // Created first, so a plain created_at DESC sort would put it last.
        $featured = $this->publicProfile(['content' => ['is_showcase' => true]]);
        $this->publicProfile();
        $this->publicProfile();

        $ids = collect(Livewire::test(ProfileList::class)->instance()->profiles()->items())->pluck('id');

        $this->assertSame($featured->id, $ids->first());
    }

    public function test_filtering_drops_the_featured_ordering_and_still_filters_correctly(): void
    {
        $this->publicProfile(['content' => ['is_showcase' => true], 'age' => 44]);
        $young = $this->publicProfile(['age' => 22]);

        $ids = collect(
            Livewire::test(ProfileList::class)
                ->set('ageGroup', '18-25')
                ->instance()
                ->profiles()
                ->items()
        )->pluck('id');

        $this->assertSame([$young->id], $ids->all());
    }

    /**
     * Impressions used to be logged by ProfileController against its own,
     * separate query — a different set of profiles from the ones this component
     * rendered. They must describe what was actually on screen.
     */
    public function test_impressions_are_recorded_for_the_profiles_actually_rendered(): void
    {
        $a = $this->publicProfile();
        $b = $this->publicProfile();

        Livewire::test(ProfileList::class);

        $seen = ProfileView::where('type', ProfileView::TYPE_IMPRESSION)->pluck('profile_id')->unique()->sort()->values();

        $this->assertSame([$a->id, $b->id], $seen->all());
    }

    public function test_a_provider_does_not_record_impressions_for_her_own_profile(): void
    {
        $owner = User::factory()->create(['gender' => 'female']);
        $own = Profile::factory()->for($owner)->create(['status' => 'approved', 'is_public' => true]);
        $other = $this->publicProfile();

        Livewire::actingAs($owner)->test(ProfileList::class);

        $seen = ProfileView::where('type', ProfileView::TYPE_IMPRESSION)->pluck('profile_id')->unique();

        $this->assertTrue($seen->contains($other->id));
        $this->assertFalse($seen->contains($own->id), 'A provider must not inflate her own impressions.');
    }

    public function test_homepage_hero_shows_real_registration_counts(): void
    {
        User::factory()->count(3)->create(['gender' => 'female']);
        User::factory()->count(2)->create(['gender' => 'male']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewHas('girlsCount', 3);
        $response->assertViewHas('gentsCount', 2);
        $response->assertDontSee('1 420');
        $response->assertDontSee('382 ');
    }
}
