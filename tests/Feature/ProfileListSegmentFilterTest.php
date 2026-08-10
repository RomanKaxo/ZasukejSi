<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Profile;
use App\Models\Segment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileListSegmentFilterTest extends TestCase
{
    use RefreshDatabase;

    private function approvedPublicProfile(string $name): Profile
    {
        return Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'status' => 'approved',
            'is_public' => true,
            'display_name' => $name,
        ]);
    }

    public function test_filtering_by_segment_only_returns_matching_profiles(): void
    {
        $segment = Segment::factory()->create();
        $matching = $this->approvedPublicProfile('Has Segment');
        $matching->segments()->attach($segment->id);
        $this->approvedPublicProfile('No Segment');

        Livewire::test(\App\Livewire\ProfileList::class)
            ->set('segmentId', $segment->id)
            ->assertSee('Has Segment')
            ->assertDontSee('No Segment');
    }

    public function test_segments_are_eager_loaded_to_avoid_n_plus_one(): void
    {
        $segment = Segment::factory()->create();
        for ($i = 0; $i < 3; $i++) {
            $this->approvedPublicProfile("Profile {$i}")->segments()->attach($segment->id);
        }

        // Each ->set() call is its own Livewire request/render round-trip, so
        // query-log capture starts only around the final render (the one that
        // forces the non-showcase branch) to isolate a single render's queries.
        $component = Livewire::test(\App\Livewire\ProfileList::class)
            ->set('region', '');

        \Illuminate\Support\Facades\DB::enableQueryLog();
        $component->set('ageMin', '18');
        $queries = \Illuminate\Support\Facades\DB::getQueryLog();
        \Illuminate\Support\Facades\DB::disableQueryLog();

        $segmentQueries = collect($queries)->filter(fn ($q) => str_contains($q['query'], 'profile_segment'));
        // Exactly one eager-load query for all 3 profiles' segments (not zero -
        // proving the eager load actually ran - and not 3 - proving no N+1).
        $this->assertSame(1, $segmentQueries->count());
    }

    public function test_reset_filters_clears_segment_id_and_active_filters_count(): void
    {
        $segment = Segment::factory()->create();

        $component = Livewire::test(\App\Livewire\ProfileList::class)
            ->set('segmentId', $segment->id);

        $this->assertSame(1, $component->instance()->activeFiltersCount());

        $component->call('resetFilters')
            ->assertSet('segmentId', '');

        $this->assertSame(0, $component->instance()->activeFiltersCount());
    }

    public function test_selecting_a_segment_resets_pagination_to_first_page(): void
    {
        $segment = Segment::factory()->create();

        Livewire::test(\App\Livewire\ProfileList::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('segmentId', $segment->id)
            ->assertSet('paginators.page', 1);
    }
}
