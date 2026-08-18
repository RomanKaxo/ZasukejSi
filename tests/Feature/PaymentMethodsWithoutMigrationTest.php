<?php

namespace Tests\Feature;

use App\Filament\Pages\ManagePaymentMethods;
use App\Models\Profile;
use App\Models\SubscriptionType;
use App\Models\User;
use App\Services\Payments\PaymentMethods;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Kód je nasazený, migrace ještě neproběhly.
 *
 * Je to normálních pár minut života každého vydání a nesmí to nic shodit —
 * ani administraci, a hlavně ne stránku, kde si zákazník kupuje předplatné.
 * Ta s platebními metodami do toho rána neměla nic společného.
 */
class PaymentMethodsWithoutMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
        $this->seed(RoleSeeder::class);

        // Přesně stav serveru před `php artisan migrate`.
        Schema::drop('payment_methods');
    }

    public function test_the_admin_page_opens_and_says_what_to_do(): void
    {
        $admin = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
        $admin->syncRoles(['super_admin', 'admin']);

        $this->actingAs($admin->fresh())
            ->get('/admin/manage-payment-methods')
            ->assertSuccessful();
    }

    public function test_saving_is_refused_instead_of_exploding(): void
    {
        $admin = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
        $admin->syncRoles(['super_admin', 'admin']);

        \Livewire\Livewire::actingAs($admin->fresh())
            ->test(ManagePaymentMethods::class)
            ->call('save')
            ->assertHasNoErrors();
    }

    /** To horší: stránka, kde zákazník kupuje předplatné. */
    public function test_the_subscription_page_still_works_for_a_customer(): void
    {
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);
        Profile::factory()->create(['user_id' => $user->id]);

        SubscriptionType::create([
            'name' => ['cs' => 'VIP', 'en' => 'VIP'],
            'slug' => 'vip',
            'audience' => 'profile',
            'price' => 500,
            'price_czk' => 500,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        $this->actingAs($user->fresh())
            ->get('/account/subscription')
            ->assertSuccessful();
    }

    public function test_the_membership_page_still_works_for_a_member(): void
    {
        $user = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);

        SubscriptionType::create([
            'name' => ['cs' => 'Premium', 'en' => 'Premium'],
            'slug' => 'premium',
            'audience' => 'member',
            'price' => 300,
            'price_czk' => 300,
            'duration_days' => 30,
            'is_active' => true,
        ]);

        $this->actingAs($user->fresh())
            ->get('/account/member/membership')
            ->assertSuccessful();
    }

    /** Bez tabulky nejsou žádné metody — a checkout se chová jako dřív. */
    public function test_no_methods_are_reported(): void
    {
        $this->assertFalse(PaymentMethods::ready());
        $this->assertTrue(PaymentMethods::available()->isEmpty());
        $this->assertNull(PaymentMethods::find('stripe'));
    }
}
