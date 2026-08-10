<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Segment;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileSegmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_segments_includes_manually_assigned_active_segments(): void
    {
        $profile = Profile::factory()->for(\App\Models\User::factory())->create();
        $active = Segment::factory()->create(['is_active' => true, 'slug' => 'nova']);
        $inactive = Segment::factory()->create(['is_active' => false, 'slug' => 'archiv']);
        $profile->segments()->attach([$active->id, $inactive->id]);

        $slugs = $profile->fresh()->allSegments()->pluck('slug');

        $this->assertTrue($slugs->contains('nova'));
        $this->assertFalse($slugs->contains('archiv'));
    }

    public function test_all_segments_includes_derived_vip_segment_when_active_subscription_exists(): void
    {
        $profile = Profile::factory()->for(\App\Models\User::factory())->create();
        $type = SubscriptionType::create([
            'name' => ['cs' => 'Elite', 'en' => 'Elite'],
            'slug' => 'elite-test',
            'price' => 100,
            'duration_days' => 30,
            'is_active' => true,
        ]);
        Subscription::create([
            'profile_id' => $profile->id,
            'subscription_type_id' => $type->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
        ]);

        $segments = $profile->fresh()->allSegments();

        $this->assertTrue($segments->contains('slug', 'vip'));
        $this->assertTrue($segments->firstWhere('slug', 'vip')['is_vip']);
    }

    public function test_all_segments_has_no_duplicates_and_no_vip_without_subscription(): void
    {
        $profile = Profile::factory()->for(\App\Models\User::factory())->create();
        $segment = Segment::factory()->create(['slug' => 'top-lokalita']);
        $profile->segments()->attach($segment->id);

        $segments = $profile->fresh()->allSegments();

        $this->assertCount(1, $segments);
        $this->assertFalse($segments->contains('slug', 'vip'));
    }
}
