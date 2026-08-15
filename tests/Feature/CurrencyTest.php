<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Profile;
use App\Models\Service;
use App\Models\User;
use App\Support\Currencies;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
        Currency::flush();
    }

    private function profile(): Profile
    {
        return Profile::factory()->create([
            'user_id' => User::factory()->create(['gender' => 'female'])->id,
        ]);
    }

    private function service(string $name = 'Masáž'): Service
    {
        return Service::create([
            'name' => ['cs' => $name, 'en' => $name],
            'description' => ['cs' => '', 'en' => ''],
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    public function test_the_admin_can_add_a_currency_and_it_becomes_available(): void
    {
        Currency::create([
            'code' => 'gbp',
            'symbol' => '£',
            'name' => ['cs' => 'Libra', 'en' => 'Pound'],
            'exchange_rate' => 0.034,
            'sort_order' => 4,
        ]);
        Currency::flush();

        // Codes are stored uppercase whatever the admin typed.
        $this->assertContains('GBP', Currencies::codes());
        $this->assertSame('£', Currencies::symbol('GBP'));
    }

    public function test_turning_a_currency_off_removes_it_from_the_offer(): void
    {
        Currency::where('code', 'USD')->update(['is_active' => false]);
        Currency::flush();

        $this->assertNotContains('USD', Currencies::codes());
        // The stored amounts are untouched; only the offer changes.
        $this->assertDatabaseHas('currencies', ['code' => 'USD']);
    }

    public function test_only_one_currency_can_be_the_base(): void
    {
        $eur = Currency::where('code', 'EUR')->firstOrFail();
        $eur->update(['is_base' => true]);
        Currency::flush();

        $this->assertSame(1, Currency::where('is_base', true)->count());
        $this->assertSame('EUR', Currency::base()->code);
        // The base is its own yardstick.
        $this->assertSame('1.000000', $eur->fresh()->exchange_rate);
    }

    public function test_conversion_goes_through_the_base(): void
    {
        $this->assertSame(80.0, Currency::convert(2000, 'CZK', 'EUR'));
        $this->assertSame(2000.0, Currency::convert(80, 'EUR', 'CZK'));
        // EUR -> USD crosses the base rather than needing its own rate.
        $this->assertSame(88.0, Currency::convert(80, 'EUR', 'USD'));
    }

    public function test_an_unknown_currency_converts_to_null_rather_than_zero(): void
    {
        $this->assertNull(Currency::convert(100, 'CZK', 'JPY'));
    }

    public function test_a_service_can_be_priced_in_several_currencies(): void
    {
        $profile = $this->profile();
        $service = $this->service();

        $profile->services()->sync([
            $service->id => ['prices' => json_encode(['CZK' => 2000, 'EUR' => 79])],
        ]);
        $profile->load('services');

        $pivot = $profile->services->firstWhere('id', $service->id);

        $this->assertSame(2000.0, $profile->servicePrice($pivot, 'CZK'));
        // The euro amount is the one that was typed, not 80 from the rate.
        $this->assertSame(79.0, $profile->servicePrice($pivot, 'EUR'));
    }

    public function test_a_missing_currency_is_not_invented_unless_asked_for(): void
    {
        $profile = $this->profile();
        $service = $this->service();

        $profile->services()->sync([
            $service->id => ['prices' => json_encode(['CZK' => 2000])],
        ]);
        $profile->load('services');
        $pivot = $profile->services->firstWhere('id', $service->id);

        $this->assertNull($profile->servicePrice($pivot, 'EUR'));

        $profile->auto_convert_prices = true;

        $this->assertSame(80.0, $profile->servicePrice($pivot, 'EUR'));
    }

    public function test_a_typed_price_is_never_replaced_by_the_rate(): void
    {
        $profile = $this->profile();
        $profile->auto_convert_prices = true;
        $service = $this->service();

        // 100 EUR is deliberately not what the rate would produce from 2000 CZK.
        $profile->services()->sync([
            $service->id => ['prices' => json_encode(['CZK' => 2000, 'EUR' => 100])],
        ]);
        $profile->load('services');
        $pivot = $profile->services->firstWhere('id', $service->id);

        $this->assertSame(100.0, $profile->servicePrice($pivot, 'EUR'));
    }

    public function test_amounts_are_formatted_the_way_each_currency_is_written(): void
    {
        $this->assertSame('2 000 Kč', Currencies::format(2000, 'CZK'));
        $this->assertSame('€80', Currencies::format(80, 'EUR'));
        $this->assertSame('$99.50', Currencies::format(99.5, 'USD'));
    }

    public function test_a_locale_whose_currency_is_off_falls_back_to_the_base(): void
    {
        Currency::where('code', 'USD')->update(['is_active' => false]);
        Currency::flush();

        // Russian is quoted in dollars; with those off it must not quote in a
        // currency that is no longer on offer.
        $this->assertSame('CZK', Currencies::forLocale('ru'));
    }
}
