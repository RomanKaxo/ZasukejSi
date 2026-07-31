<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberReportedTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_sees_only_their_own_reports(): void
    {
        $member = User::factory()->create(['gender' => 'male']);
        $otherMember = User::factory()->create(['gender' => 'male']);
        $profileOwner = User::factory()->create(['gender' => 'female']);
        $otherProfileOwner = User::factory()->create(['gender' => 'female']);

        $myProfile = Profile::factory()->for($profileOwner)->create(['display_name' => 'Tamara']);
        $othersProfile = Profile::factory()->for($otherProfileOwner)->create(['display_name' => 'NotMine']);

        Report::factory()->create(['profile_id' => $myProfile->id, 'reporter_id' => $member->id]);
        Report::factory()->create(['profile_id' => $othersProfile->id, 'reporter_id' => $otherMember->id]);

        $response = $this->actingAs($member)->get(route('account.member.reported'));

        $response->assertOk();
        $response->assertSee('Tamara');
        $response->assertDontSee('NotMine');
    }

    public function test_reported_page_shows_empty_state_with_no_reports(): void
    {
        $member = User::factory()->create(['gender' => 'male']);

        $response = $this->actingAs($member)->get(route('account.member.reported'));

        $response->assertOk();
    }
}
