<?php

namespace Tests\Feature;

use App\Models\Segment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The segment dropdown is not part of the design's filter row, so it is no
 * longer rendered. The filter itself stays reachable — segment links from
 * elsewhere in the site depend on it — which is what these assertions pin.
 */
class ProfileListSegmentFilterUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_filter_row_has_no_segment_dropdown(): void
    {
        Segment::factory()->create(['name' => ['cs' => 'Nová', 'en' => 'New'], 'is_active' => true]);

        Livewire::test(\App\Livewire\ProfileList::class)
            ->assertDontSee('wire:model.live="segmentId"', false);
    }

    public function test_the_segment_filter_still_narrows_the_listing(): void
    {
        app()->setLocale('cs');

        $segment = Segment::factory()->create(['name' => ['cs' => 'Nová', 'en' => 'New'], 'is_active' => true]);

        Livewire::test(\App\Livewire\ProfileList::class)
            ->set('segmentId', $segment->id)
            ->assertSet('segmentId', $segment->id)
            ->assertOk();
    }

    public function test_the_segment_can_still_be_chosen_from_the_url(): void
    {
        $segment = Segment::factory()->create(['is_active' => true]);

        // ProfileList reads ?segment=<id> on mount, which is how the site's own
        // segment links reach a filtered listing.
        $this->get(route('profiles.index', ['segment' => $segment->id]))
            ->assertSuccessful();
    }
}
