<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSettings;
use App\Models\Setting;
use App\Models\User;
use App\Support\TopRatedLock;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The switch itself, on Nastavení systému.
 *
 * The page's fields load deferred, so this goes through Livewire rather than
 * asserting on the HTTP response.
 */
class TopRatedLockSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'super_admin']);

        $admin = User::factory()->create(['email' => 'test@example.com']);
        $admin->syncRoles(['admin', 'super_admin']);

        return $admin;
    }

    public function test_the_form_opens_on_the_current_mode(): void
    {
        Setting::set(TopRatedLock::KEY, TopRatedLock::MODE_VIP);

        Livewire::actingAs($this->admin())
            ->test(ManageSettings::class)
            ->assertFormSet(['top_rated_lock_mode' => TopRatedLock::MODE_VIP]);
    }

    public function test_switching_to_vip_is_stored(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ManageSettings::class)
            // `set` rather than `fillForm`: the latter re-fills the whole form
            // and leaves the radio on whatever mount() put there.
            ->set('data.top_rated_lock_mode', TopRatedLock::MODE_VIP)
            ->call('save')
            ->assertHasNoFormErrors();

        Setting::flush();

        $this->assertSame(TopRatedLock::MODE_VIP, TopRatedLock::mode());
    }

    public function test_switching_back_to_premium_is_stored(): void
    {
        Setting::set(TopRatedLock::KEY, TopRatedLock::MODE_VIP);

        Livewire::actingAs($this->admin())
            ->test(ManageSettings::class)
            ->set('data.top_rated_lock_mode', TopRatedLock::MODE_PREMIUM)
            ->call('save')
            ->assertHasNoFormErrors();

        Setting::flush();

        $this->assertSame(TopRatedLock::MODE_PREMIUM, TopRatedLock::mode());
    }

    public function test_both_modes_are_offered(): void
    {
        $this->assertSame(
            [TopRatedLock::MODE_PREMIUM, TopRatedLock::MODE_VIP],
            array_keys(TopRatedLock::options()),
        );
    }
}
