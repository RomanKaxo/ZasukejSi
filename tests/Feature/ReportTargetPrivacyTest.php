<?php

namespace Tests\Feature;

use App\Livewire\ReportProfileModal;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportTargetPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public static function hiddenProfiles(): array
    {
        return [['approved', false], ['pending', true], ['rejected', true], ['archived', true]];
    }

    #[DataProvider('hiddenProfiles')]
    public function test_manipulated_report_target_cannot_disclose_a_hidden_profile(string $status, bool $public): void
    {
        $member = User::factory()->create(['gender' => 'male']);
        $profile = Profile::factory()->for(User::factory()->create(['gender' => 'female']))
            ->create(['status' => $status, 'is_public' => $public]);

        Livewire::actingAs($member)->test(ReportProfileModal::class)
            ->set('profileId', $profile->id)
            ->set('email', $member->email)
            ->set('message', 'Manipulated private profile target')
            ->call('submit')->assertHasErrors('email')->assertSet('submitted', false);

        $this->assertDatabaseCount('profile_reports', 0);
    }

    public function test_profile_unpublished_after_dialog_open_is_rechecked_at_submission(): void
    {
        $profile = Profile::factory()->for(User::factory())->approved()->public()->create();
        $modal = Livewire::test(ReportProfileModal::class)->call('show', $profile->id)
            ->set('email', 'guest@example.test')->set('message', 'A report after unpublishing');
        $profile->update(['is_public' => false]);

        $modal->call('submit')->assertHasErrors('email');
        $this->assertDatabaseCount('profile_reports', 0);
    }

    public function test_public_profile_still_accepts_an_anonymous_report(): void
    {
        $profile = Profile::factory()->for(User::factory())->approved()->public()->create();
        Livewire::test(ReportProfileModal::class)->call('show', $profile->id)
            ->set('email', 'guest@example.test')->set('message', 'A legitimate public report')
            ->call('submit')->assertHasNoErrors()->assertSet('submitted', true);
        $this->assertDatabaseHas('profile_reports', ['profile_id' => $profile->id, 'reporter_id' => null]);
    }

    public function test_owner_and_admin_can_report_a_profile_they_can_preview(): void
    {
        $owner = User::factory()->create(['gender' => 'female']);
        $profile = Profile::factory()->for($owner)->create(['status' => 'pending', 'is_public' => false]);
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        foreach ([$owner, $admin] as $reporter) {
            Livewire::actingAs($reporter)->test(ReportProfileModal::class)->call('show', $profile->id)
                ->set('email', $reporter->email)->set('message', 'Report from an authorized preview')
                ->call('submit')->assertHasNoErrors()->assertSet('submitted', true);
            $this->assertDatabaseHas('profile_reports', ['profile_id' => $profile->id, 'reporter_id' => $reporter->id]);
        }
    }
}
