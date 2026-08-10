<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Segment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SegmentAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_translatable_name_falls_back_to_configured_fallback_locale(): void
    {
        app()->setLocale('en');
        $segment = Segment::factory()->create(['name' => ['cs' => 'Pouze česky']]);
        $profile = Profile::factory()->for(\App\Models\User::factory())->create();
        $profile->segments()->attach($segment->id);

        $name = $segment->fresh()->getTranslation('name', 'en', useFallbackLocale: true);

        $this->assertSame('Pouze česky', $name);
    }

    public function test_deleting_a_segment_does_not_break_allsegments_or_leave_orphan_pivot_rows(): void
    {
        $segment = Segment::factory()->create();
        $profile = Profile::factory()->for(\App\Models\User::factory())->create();
        $profile->segments()->attach($segment->id);

        $segment->delete();

        $this->assertDatabaseMissing('profile_segment', ['segment_id' => $segment->id]);
        $this->assertCount(0, $profile->fresh()->allSegments());
    }

    public function test_deleting_a_profile_does_not_leave_orphan_pivot_rows(): void
    {
        $segment = Segment::factory()->create();
        $profile = Profile::factory()->for(\App\Models\User::factory())->create();
        $profile->segments()->attach($segment->id);
        $profileId = $profile->id;

        $profile->forceDelete();

        $this->assertDatabaseMissing('profile_segment', ['profile_id' => $profileId]);
    }

    public function test_loading_many_profiles_with_segments_does_not_n_plus_one(): void
    {
        $segment = Segment::factory()->create();
        collect(range(1, 5))
            ->map(fn () => Profile::factory()->for(\App\Models\User::factory())->create())
            ->each(fn (Profile $p) => $p->segments()->attach($segment->id));

        DB::enableQueryLog();
        // withExists('activeSubscription as is_vip') is the existing app-wide
        // convention (already used by ProfileList/CountryProfiles/ProfileSlider)
        // for computing VIP status without an exists() query per row.
        $profiles = Profile::with('segments')->withExists('activeSubscription as is_vip')->get();
        foreach ($profiles as $profile) {
            $profile->allSegments();
        }
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        // 1 query for profiles (is_vip computed inline via withExists) + 1 query
        // for the eager-loaded segments pivot join, regardless of how many
        // profiles there are.
        $this->assertLessThanOrEqual(2, $queryCount);
    }
}
