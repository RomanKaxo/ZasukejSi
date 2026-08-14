<?php

namespace Tests\Feature;

use App\Livewire\ProfileStatistics;
use App\Models\Profile;
use App\Models\ProfileView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The provider statistics chart was a mock-up: sixteen fixed September labels
 * and two hardcoded value arrays. Real traffic was being recorded in
 * `profile_views` the whole time, and ProfileView::getDailyStats() — written for
 * exactly this — was never called.
 */
class ProfileStatisticsRealDataTest extends TestCase
{
    use RefreshDatabase;

    private function provider(): User
    {
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);
        Profile::factory()->for($user)->create(['status' => 'approved', 'is_public' => true]);

        return $user;
    }

    private function recordView(Profile $profile, string $type, string $date, int $times = 1): void
    {
        for ($i = 0; $i < $times; $i++) {
            ProfileView::create([
                'profile_id' => $profile->id,
                'type' => $type,
                'viewed_date' => $date,
            ]);
        }
    }

    public function test_chart_values_come_from_the_profile_views_table(): void
    {
        $user = $this->provider();
        $profile = $user->profile;
        $today = now()->toDateString();

        $this->recordView($profile, ProfileView::TYPE_IMPRESSION, $today, 4);

        $component = Livewire::actingAs($user)->test(ProfileStatistics::class, ['variant' => 'homepage'])->instance();

        $index = array_search(now()->format('j. n.'), $component->chartLabels, true);
        $this->assertNotFalse($index);
        $this->assertSame(4, $component->chartValues[$index]);
    }

    public function test_the_detail_variant_charts_clicks_not_impressions(): void
    {
        $user = $this->provider();
        $profile = $user->profile;
        $today = now()->toDateString();

        $this->recordView($profile, ProfileView::TYPE_IMPRESSION, $today, 7);
        $this->recordView($profile, ProfileView::TYPE_CLICK, $today, 2);

        $detail = Livewire::actingAs($user)->test(ProfileStatistics::class, ['variant' => 'detail'])->instance();
        $index = array_search(now()->format('j. n.'), $detail->chartLabels, true);

        $this->assertSame(2, $detail->chartValues[$index]);
    }

    public function test_no_hardcoded_september_labels_remain(): void
    {
        $component = Livewire::actingAs($this->provider())->test(ProfileStatistics::class)->instance();

        $this->assertNotSame(
            ['10. 9.', '11. 9.', '12. 9.'],
            array_slice($component->chartLabels, 0, 3),
            'The chart is still emitting the old fixed September labels.'
        );
        $this->assertSame(now()->startOfMonth()->format('j. n.'), $component->chartLabels[0]);
    }

    public function test_a_profile_with_no_traffic_charts_zeroes_rather_than_invented_numbers(): void
    {
        $component = Livewire::actingAs($this->provider())->test(ProfileStatistics::class)->instance();

        $this->assertNotEmpty($component->chartValues);
        $this->assertSame(0, array_sum($component->chartValues));
    }

    /**
     * mount() used to fall back to Profile::first() when the user had none,
     * charting somebody else's traffic on their page.
     */
    public function test_a_user_without_a_profile_sees_no_data_at_all(): void
    {
        $member = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
        $stranger = $this->provider();
        $this->recordView($stranger->profile, ProfileView::TYPE_IMPRESSION, now()->toDateString(), 9);

        $component = Livewire::actingAs($member)->test(ProfileStatistics::class)->instance();

        $this->assertNull($component->profileId);
        $this->assertSame([], $component->chartValues);
    }

    public function test_month_navigation_moves_the_range(): void
    {
        $user = $this->provider();
        $lastMonth = now()->subMonthNoOverflow()->startOfMonth();

        $this->recordView($user->profile, ProfileView::TYPE_IMPRESSION, $lastMonth->toDateString(), 3);

        $component = Livewire::actingAs($user)->test(ProfileStatistics::class);

        // Sanity-check the data layer first, so a failure here points at the
        // component rather than at the query.
        $this->assertSame(
            [$lastMonth->toDateString() => 3],
            ProfileView::getDailyStats(
                $user->profile->id,
                $lastMonth->toDateString(),
                $lastMonth->copy()->endOfMonth()->toDateString(),
                ProfileView::TYPE_IMPRESSION
            )
        );

        $component->call('previousMonth');
        $instance = $component->instance();

        $this->assertSame($lastMonth->format('Y-m'), $instance->month);
        $this->assertSame(3, $instance->chartValues[0]);
    }

    public function test_navigation_cannot_go_past_the_current_month(): void
    {
        $component = Livewire::actingAs($this->provider())->test(ProfileStatistics::class);
        $component->call('nextMonth');

        $this->assertSame(now()->startOfMonth()->format('Y-m'), $component->instance()->month);
    }

    public function test_axis_scale_adapts_to_the_data(): void
    {
        $user = $this->provider();
        $this->recordView($user->profile, ProfileView::TYPE_IMPRESSION, now()->toDateString(), 3);

        $component = Livewire::actingAs($user)->test(ProfileStatistics::class)->instance();

        $this->assertGreaterThanOrEqual(3, $component->yAxisMax);
        $this->assertLessThan(120, $component->yAxisMax, 'The axis is still pinned to the old hardcoded maximum.');
    }
}
