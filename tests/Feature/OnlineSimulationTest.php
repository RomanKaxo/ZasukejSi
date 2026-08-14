<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The online badge used to fabricate a status for ~30% of profiles from a
 * magic number baked into the model. The share is now configuration, and 0
 * turns the fallback off completely.
 */
class OnlineSimulationTest extends TestCase
{
    use RefreshDatabase;

    private function offlineProfile(): Profile
    {
        $user = User::factory()->create(['last_activity' => time() - 86400]);

        return Profile::factory()->create(['user_id' => $user->id]);
    }

    public function test_simulation_disabled_means_offline_users_are_offline(): void
    {
        config(['site.online_simulation_percent' => 0]);

        $profiles = collect(range(1, 25))->map(fn () => $this->offlineProfile());

        foreach ($profiles as $profile) {
            $this->assertFalse($profile->isOnline());
        }
    }

    public function test_full_simulation_marks_every_offline_profile_online(): void
    {
        config(['site.online_simulation_percent' => 100]);

        $this->assertTrue($this->offlineProfile()->isOnline());
    }

    public function test_real_activity_wins_over_a_disabled_simulation(): void
    {
        config(['site.online_simulation_percent' => 0]);

        $user = User::factory()->create(['last_activity' => time()]);
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($profile->fresh()->isOnline());
    }
}
