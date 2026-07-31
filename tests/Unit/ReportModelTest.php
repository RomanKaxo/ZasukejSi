<?php

namespace Tests\Unit;

use App\Models\Profile;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_belongs_to_profile_and_reporter(): void
    {
        $reporter = User::factory()->create(['gender' => 'male']);
        $profileOwner = User::factory()->create(['gender' => 'female']);
        $profile = Profile::factory()->for($profileOwner)->create();

        $report = Report::create([
            'profile_id' => $profile->id,
            'reporter_id' => $reporter->id,
            'reason' => 'Test reason',
            'allegations' => ['theft', 'fraud'],
        ]);

        $this->assertTrue($report->profile->is($profile));
        $this->assertTrue($report->reporter->is($reporter));
        $this->assertSame(['theft', 'fraud'], $report->fresh()->allegations);
    }

    public function test_allegation_categories_constant_has_six_fixed_values(): void
    {
        $this->assertSame(
            ['theft', 'photo_mismatch', 'fraud', 'threats', 'fake_profile', 'inappropriate_behavior'],
            Report::ALLEGATION_CATEGORIES
        );
    }
}
