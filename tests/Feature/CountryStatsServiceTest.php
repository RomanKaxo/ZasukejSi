<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Profile;
use App\Models\User;
use App\Services\CountryStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The country lists used to live in three hardcoded arrays that disagreed with
 * each other and with the database. One of them listed the same eight countries
 * three times over, each repetition with a different invented count.
 *
 * These tests pin down the properties that replacement has to keep:
 * uniqueness, real counts, countries surviving at zero, and counts that agree
 * with the listing the user actually lands on.
 */
class CountryStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): CountryStatsService
    {
        CountryStatsService::flush();

        return app(CountryStatsService::class);
    }

    private function listedProfile(string $countryCode, ?string $city = null): Profile
    {
        return Profile::factory()->for(User::factory())->create([
            'status' => 'approved',
            'is_public' => true,
            'verified_at' => now(),
            'country_code' => $countryCode,
            'city' => $city,
        ]);
    }

    public function test_each_country_appears_exactly_once(): void
    {
        Country::create(['code' => 'CZ', 'sort_order' => 10]);
        Country::create(['code' => 'DE', 'sort_order' => 20]);
        $this->listedProfile('CZ');
        $this->listedProfile('CZ');
        $this->listedProfile('DE');

        $codes = $this->service()->countries()->pluck('code');

        $this->assertSame(['CZ', 'DE'], $codes->all());
        $this->assertSame($codes->count(), $codes->unique()->count());
    }

    public function test_a_visible_country_without_profiles_stays_in_the_list_with_zero(): void
    {
        Country::create(['code' => 'AD', 'sort_order' => 10]);

        $andorra = $this->service()->countries()->firstWhere('code', 'AD');

        $this->assertNotNull($andorra, 'A visible country must remain in the list even with no profiles.');
        $this->assertSame(0, $andorra->profiles_count);
    }

    public function test_hidden_countries_are_excluded(): void
    {
        Country::create(['code' => 'CZ', 'sort_order' => 10, 'is_visible' => true]);
        Country::create(['code' => 'DE', 'sort_order' => 20, 'is_visible' => false]);

        $this->assertSame(['CZ'], $this->service()->countries()->pluck('code')->all());
    }

    /**
     * The sidebar count is a promise about the listing behind the link, so it
     * must use the listing's own predicate: approved + public + verified.
     */
    public function test_counts_match_the_public_listing_predicate(): void
    {
        Country::create(['code' => 'CZ', 'sort_order' => 10]);

        $this->listedProfile('CZ');
        Profile::factory()->for(User::factory())->create([
            'status' => 'pending', 'is_public' => true, 'verified_at' => now(), 'country_code' => 'CZ',
        ]);
        Profile::factory()->for(User::factory())->create([
            'status' => 'approved', 'is_public' => false, 'verified_at' => now(), 'country_code' => 'CZ',
        ]);
        Profile::factory()->for(User::factory())->create([
            'status' => 'approved', 'is_public' => true, 'verified_at' => null, 'country_code' => 'CZ',
        ]);

        $this->assertSame(1, $this->service()->countries()->firstWhere('code', 'CZ')->profiles_count);
    }

    /**
     * Codes were stored with mixed casing ("cz" and "CZ" both existed) while
     * cities are always uppercase, so lowercase profiles silently vanished from
     * every country listing. The model mutator is what keeps that from
     * recurring.
     */
    public function test_country_codes_are_normalised_to_uppercase_on_write(): void
    {
        Country::create(['code' => 'CZ', 'sort_order' => 10]);

        $profile = $this->listedProfile('cz');

        $this->assertSame('CZ', $profile->fresh()->country_code);
        $this->assertSame(1, $this->service()->countries()->firstWhere('code', 'CZ')->profiles_count);
    }

    public function test_country_model_also_normalises_its_own_code(): void
    {
        $country = Country::create(['code' => 'de', 'sort_order' => 10]);

        $this->assertSame('DE', $country->fresh()->code);
    }

    public function test_regions_come_from_cities_and_carry_real_counts(): void
    {
        Country::create(['code' => 'CZ', 'sort_order' => 10]);
        City::create(['name' => 'Praha', 'name_ascii' => 'Praha', 'country_code' => 'CZ', 'admin_name' => 'Praha']);
        City::create(['name' => 'Brno', 'name_ascii' => 'Brno', 'country_code' => 'CZ', 'admin_name' => 'Jihomoravský Kraj']);

        $this->listedProfile('CZ', 'Praha');
        $this->listedProfile('CZ', 'Praha');
        $this->listedProfile('CZ', 'Brno');

        $regions = $this->service()->regionsFor('CZ');

        $this->assertSame(['Praha', 'Jihomoravský Kraj'], $regions->pluck('name')->all());
        $this->assertSame(2, $regions->firstWhere('name', 'Praha')->profiles_count);
        $this->assertSame(1, $regions->firstWhere('name', 'Jihomoravský Kraj')->profiles_count);
    }

    public function test_country_name_falls_back_to_the_iso_translation_files(): void
    {
        Country::create(['code' => 'CZ', 'sort_order' => 10]);

        app()->setLocale('en');
        CountryStatsService::flush();
        $this->assertSame('Czechia', app(CountryStatsService::class)->countries()->firstWhere('code', 'CZ')->name);
    }

    public function test_name_override_wins_over_the_iso_name(): void
    {
        Country::create(['code' => 'CZ', 'sort_order' => 10, 'name_override' => ['cs' => 'Česká republika']]);

        app()->setLocale('cs');
        CountryStatsService::flush();
        $this->assertSame('Česká republika', app(CountryStatsService::class)->countries()->firstWhere('code', 'CZ')->name);
    }

    /**
     * Counts are memoised because the lists render on every public page; saving
     * a profile must drop that cache or the numbers go stale.
     */
    public function test_saving_a_profile_invalidates_the_cached_counts(): void
    {
        Country::create(['code' => 'CZ', 'sort_order' => 10]);
        $service = $this->service();

        $this->assertSame(0, $service->countries()->firstWhere('code', 'CZ')->profiles_count);

        $this->listedProfile('CZ');

        $this->assertSame(1, app(CountryStatsService::class)->countries()->firstWhere('code', 'CZ')->profiles_count);
    }
}
