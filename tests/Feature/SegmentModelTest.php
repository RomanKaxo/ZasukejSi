<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Segment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SegmentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_segment_can_be_attached_to_a_profile(): void
    {
        $segment = Segment::factory()->create(['name' => ['cs' => 'Nová', 'en' => 'New']]);
        $user = User::factory()->create(['gender' => 'female']);
        $profile = Profile::factory()->for($user)->create();

        $profile->segments()->attach($segment->id);

        $this->assertTrue($profile->fresh()->segments->contains('id', $segment->id));
        $this->assertTrue($segment->fresh()->profiles->contains('id', $profile->id));
    }

    public function test_scope_active_excludes_inactive_segments(): void
    {
        Segment::factory()->create(['is_active' => true, 'sort_order' => 1]);
        Segment::factory()->create(['is_active' => false, 'sort_order' => 2]);

        $this->assertCount(1, Segment::active()->get());
    }

    public function test_scope_ordered_sorts_by_sort_order(): void
    {
        Segment::factory()->create(['name' => ['cs' => 'B', 'en' => 'B'], 'sort_order' => 2]);
        Segment::factory()->create(['name' => ['cs' => 'A', 'en' => 'A'], 'sort_order' => 1]);

        $names = Segment::ordered()->pluck('sort_order')->all();

        $this->assertSame([1, 2], $names);
    }
}
