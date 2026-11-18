<?php

namespace Tests\Feature;

use App\Livewire\SearchProfiles;
use App\Models\Country;
use App\Services\CountryStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pass 5 (cross-browser / responsive check, 18. 11. 2026).
 */
class CrossBrowserPass5Test extends TestCase
{
    use RefreshDatabase;

    /**
     * The country sidebar links to ?country_code=CZ (upper case) while the
     * search options carry lower-case codes for flagcdn. The exact comparison
     * never matched, so the field fell back to the first country and read
     * "Albania" above Czech results.
     */
    public function test_english_search_resolves_upper_case_country_code_from_url(): void
    {
        Country::create(['code' => 'AL', 'sort_order' => 1]);
        Country::create(['code' => 'CZ', 'sort_order' => 2]);
        CountryStatsService::flush();
        app()->setLocale('en');

        $component = Livewire::withQueryParams(['country_code' => 'CZ'])
            ->test(SearchProfiles::class);

        $this->assertSame('cz', $component->get('country_code'));
        $this->assertNotSame('Albania', $component->get('country'));
        $this->assertStringContainsString('Czech', (string) $component->get('country'));
    }
}
