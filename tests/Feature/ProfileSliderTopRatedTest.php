<?php

namespace Tests\Feature;

use App\Livewire\ProfileSlider;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The profile detail carries two "top rated" sliders, one headed "this month"
 * and one "all time". They were both given the month sort, so they listed the
 * same profiles — which read as a static block rather than a ranking.
 */
class ProfileSliderTopRatedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    private function profile(string $name): Profile
    {
        return Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'display_name' => ['cs' => $name, 'en' => $name],
            'status' => 'approved',
            'is_public' => true,
        ]);
    }

    private function rate(Profile $profile, int $percentage, ?\DateTimeInterface $when = null): void
    {
        $profile->rateByPercentage(User::factory()->create(['gender' => 'male']), $percentage);

        if ($when) {
            $profile->ratings()->latest('id')->first()?->forceFill([
                'created_at' => $when,
                'updated_at' => $when,
            ])->saveQuietly();
        }
    }

    public function test_the_two_sorts_rank_differently(): void
    {
        $lastYear = $this->profile('Loni');
        $thisMonth = $this->profile('Tento měsíc');

        // Rated top, but long ago: first all time, absent this month.
        $this->rate($lastYear, 100, now()->subMonths(6));
        $this->rate($thisMonth, 40, now());

        $month = Livewire::test(ProfileSlider::class, ['sortBy' => 'rating_this_month', 'limit' => 10])
            ->instance()->profiles->pluck('id');

        $allTime = Livewire::test(ProfileSlider::class, ['sortBy' => 'rating', 'limit' => 10])
            ->instance()->profiles->pluck('id');

        // The month ranking only knows about ratings given this month.
        $this->assertTrue($month->contains($thisMonth->id));
        $this->assertFalse($month->contains($lastYear->id));

        // All time ranks the older, higher-rated profile first.
        $this->assertSame($lastYear->id, $allTime->first());
    }

    public function test_a_slider_can_leave_the_current_profile_out(): void
    {
        $current = $this->profile('Angela');
        $other = $this->profile('Lucie');

        $this->rate($current, 100);
        $this->rate($other, 70);

        $ids = Livewire::test(ProfileSlider::class, [
            'sortBy' => 'rating',
            'limit' => 10,
            'excludeProfileId' => $current->id,
        ])->instance()->profiles->pluck('id');

        $this->assertFalse($ids->contains($current->id));
        $this->assertTrue($ids->contains($other->id));
    }

    public function test_without_the_exclusion_every_profile_is_eligible(): void
    {
        $one = $this->profile('Jedna');
        $this->rate($one, 100);

        $ids = Livewire::test(ProfileSlider::class, ['sortBy' => 'rating', 'limit' => 10])
            ->instance()->profiles->pluck('id');

        $this->assertTrue($ids->contains($one->id));
    }

    public function test_the_ranking_follows_the_ratings(): void
    {
        $best = $this->profile('Nejlepší');
        $middle = $this->profile('Střední');
        $worst = $this->profile('Nejhorší');

        $this->rate($best, 100);
        $this->rate($middle, 70);
        $this->rate($worst, 30);

        $ids = Livewire::test(ProfileSlider::class, ['sortBy' => 'rating', 'limit' => 10])
            ->instance()->profiles->pluck('id')->all();

        $this->assertSame([$best->id, $middle->id, $worst->id], array_slice($ids, 0, 3));
    }
}
