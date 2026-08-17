<?php

namespace Tests\Feature;

use App\Filament\Resources\ProfileViews\Pages\ListProfileViews;
use App\Models\Profile;
use App\Models\ProfileView;
use App\Models\User;
use App\Support\ProfileViewSeries;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Návštěvnost po profilech.
 *
 * Sekce dřív vypisovala řádek za každou návštěvu — desetitisíce řádků, ve
 * kterých se nedalo najít, kdo je nejvíc vidět, protože podle toho nešlo
 * seřadit.
 */
class ProfileViewsListingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
        $this->seed(RoleSeeder::class);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['email' => 'test@example.com']);
        $admin->syncRoles(['admin', 'super_admin']);

        return $admin;
    }

    private function profileWithViews(string $name, int $recent, int $old = 0): Profile
    {
        $profile = Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
            'display_name' => ['cs' => $name, 'en' => $name],
            'status' => 'approved',
            'is_public' => true,
        ]);

        // `range(1, 0)` je [1, 0], tedy dva průchody — nula návštěv se musí
        // ošetřit podmínkou, ne maximem.
        for ($i = 0; $i < $recent; $i++) {
            ProfileView::create([
                'profile_id' => $profile->id,
                'type' => ProfileView::TYPE_IMPRESSION,
                'viewed_date' => now()->subDays(3)->toDateString(),
            ]);
        }

        for ($i = 0; $i < $old; $i++) {
            ProfileView::create([
                'profile_id' => $profile->id,
                'type' => ProfileView::TYPE_IMPRESSION,
                'viewed_date' => now()->subYears(2)->toDateString(),
            ]);
        }

        return $profile;
    }

    public function test_one_row_per_profile_not_per_visit(): void
    {
        $this->profileWithViews('Jana', 12);

        Livewire::actingAs($this->admin())
            ->test(ListProfileViews::class)
            // Dvanáct návštěv, jeden řádek.
            ->assertCountTableRecords(1);
    }

    public function test_the_most_viewed_come_first(): void
    {
        $quiet = $this->profileWithViews('Tichá', 2);
        $busy = $this->profileWithViews('Rušná', 30);

        Livewire::actingAs($this->admin())
            ->test(ListProfileViews::class)
            ->assertCanSeeTableRecords([$busy, $quiet], inOrder: true);
    }

    public function test_sorting_the_other_way_works_too(): void
    {
        $quiet = $this->profileWithViews('Tichá', 2);
        $busy = $this->profileWithViews('Rušná', 30);

        Livewire::actingAs($this->admin())
            ->test(ListProfileViews::class)
            ->sortTable('views_in_period')
            ->assertCanSeeTableRecords([$quiet, $busy], inOrder: true);
    }

    /** „Celkem" počítá i to, co je mimo okno grafu. */
    public function test_the_total_counts_everything(): void
    {
        $profile = $this->profileWithViews('Jana', 5, old: 7);

        $row = Livewire::actingAs($this->admin())
            ->test(ListProfileViews::class)
            ->instance()
            ->getTableRecords()
            ->firstWhere('id', $profile->id);

        // Za rok jich je pět, celkem dvanáct — starší návštěvy z okna vypadnou,
        // ale ze součtu ne.
        $this->assertSame(12, (int) $row->views_total);
        $this->assertSame(5, (int) $row->views_in_period);
    }

    public function test_a_year_is_drawn_by_month_and_a_month_by_day(): void
    {
        $this->assertFalse(ProfileViewSeries::isDaily(ProfileViewSeries::PERIOD_YEAR));
        $this->assertTrue(ProfileViewSeries::isDaily(ProfileViewSeries::PERIOD_MONTH));

        // Rok po měsících je třináct sloupečků, ne tři sta šedesát pět.
        $this->assertLessThanOrEqual(13, count(ProfileViewSeries::axis(ProfileViewSeries::PERIOD_YEAR)));
        $this->assertGreaterThan(25, count(ProfileViewSeries::axis(ProfileViewSeries::PERIOD_MONTH)));
    }

    public function test_all_five_periods_are_offered(): void
    {
        $this->assertSame(
            ['total', 'month', 'quarter', 'half', 'year'],
            array_keys(ProfileViewSeries::periods()),
        );
    }

    public function test_a_profile_with_no_views_still_has_a_flat_series(): void
    {
        $profile = $this->profileWithViews('Nová', 0);

        $series = ProfileViewSeries::seriesFor(
            app(ProfileViewSeries::class)->buckets([$profile->id], ProfileViewSeries::PERIOD_YEAR),
            $profile->id,
            ProfileViewSeries::PERIOD_YEAR,
        );

        $this->assertNotEmpty($series);
        $this->assertSame(0, array_sum($series));
    }

    public function test_the_series_lands_in_the_right_bucket(): void
    {
        $profile = $this->profileWithViews('Jana', 4);

        $series = ProfileViewSeries::seriesFor(
            app(ProfileViewSeries::class)->buckets([$profile->id], ProfileViewSeries::PERIOD_YEAR),
            $profile->id,
            ProfileViewSeries::PERIOD_YEAR,
        );

        $this->assertSame(4, array_sum($series));
        // Poslední měsíc je ten, ve kterém návštěvy proběhly.
        $this->assertSame(4, $series[count($series) - 1]);
    }
}
