<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\User;
use App\Services\Payments\BankTransfer;
use App\Services\Payments\PaymentMethods;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Platba bankovním převodem.
 *
 * S kartou je objednávka a platba jedna událost: brána odpoví dřív, než
 * kupující opustí stránku. Převod je oddělí o den až tři, a přesně v té mezeře
 * je celý rozdíl — objednávka existuje, zákazník svou část udělal a nikdo
 * zatím nemůže říct, jestli částka dorazila.
 *
 * Proto se tady nic neaktivuje. Aktivovat na kliknutí by znamenalo, že web
 * vydává placený produkt na to, že někdo zmáčkl tlačítko.
 */
class BankTransferPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
        $this->seed(RoleSeeder::class);

        PaymentMethods::sync();
    }

    private function enableBankTransfer(array $settings = []): PaymentMethod
    {
        $method = PaymentMethods::find(PaymentMethod::CODE_BANK_TRANSFER);

        $method->forceFill([
            'is_enabled' => true,
            'settings' => array_merge([
                'account_holder' => 'ZasukejSi s.r.o.',
                'account_number' => '123456789/0100',
                'bank_name' => 'Komerční banka',
            ], $settings),
        ])->save();

        return $method->fresh();
    }

    private function plan(): SubscriptionType
    {
        return SubscriptionType::create([
            'name' => ['cs' => 'VIP', 'en' => 'VIP'],
            'slug' => 'vip',
            'audience' => 'profile',
            'price' => 500,
            'price_czk' => 500,
            'duration_days' => 30,
            'is_active' => true,
        ]);
    }

    private function provider(): User
    {
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);

        Profile::factory()->create(['user_id' => $user->id]);

        return $user->fresh();
    }

    /** Zapnutá metoda bez čísla účtu nabízí cestu, která nikam nevede. */
    public function test_a_method_without_an_account_is_not_offered(): void
    {
        $this->enableBankTransfer(['account_number' => '', 'iban' => '']);

        $this->assertFalse(PaymentMethods::isAvailable(PaymentMethod::CODE_BANK_TRANSFER));
    }

    public function test_a_configured_method_is_offered(): void
    {
        $this->enableBankTransfer();

        $this->assertTrue(PaymentMethods::isAvailable(PaymentMethod::CODE_BANK_TRANSFER));
    }

    public function test_a_disabled_method_is_not_offered(): void
    {
        $this->enableBankTransfer();

        PaymentMethods::find(PaymentMethod::CODE_BANK_TRANSFER)->forceFill(['is_enabled' => false])->save();

        $this->assertFalse(PaymentMethods::isAvailable(PaymentMethod::CODE_BANK_TRANSFER));
    }

    /** To podstatné: objednávka vznikne, ale předplatné neběží. */
    public function test_ordering_by_transfer_creates_a_pending_subscription(): void
    {
        $this->enableBankTransfer();

        $user = $this->provider();
        $plan = $this->plan();

        $this->actingAs($user)
            ->post(route('account.subscription.checkout', $plan), [
                'payment_method' => PaymentMethod::CODE_BANK_TRANSFER,
            ])
            ->assertRedirect(route('account.subscription.index'));


        $subscription = Subscription::first();

        $this->assertNotNull($subscription);
        $this->assertSame(Subscription::STATUS_PENDING, $subscription->status);
        $this->assertSame(PaymentMethod::CODE_BANK_TRANSFER, $subscription->payment_method);
        $this->assertNull($subscription->paid_at);
        $this->assertNull($subscription->starts_at, 'Platnost nezačíná objednávkou.');
    }

    /** Bez variabilního symbolu je platba na účtu anonymní částka. */
    public function test_the_order_gets_a_payment_reference(): void
    {
        $this->enableBankTransfer();

        $this->actingAs($this->provider())
            ->post(route('account.subscription.checkout', $this->plan()), [
                'payment_method' => PaymentMethod::CODE_BANK_TRANSFER,
            ]);

        $subscription = Subscription::first();

        $this->assertNotEmpty($subscription->payment_reference);
        $this->assertMatchesRegularExpression('/^1\d{8}$/', $subscription->payment_reference);
    }

    /** Kupující musí vidět, kam poslat peníze. */
    public function test_the_payment_details_are_shown(): void
    {
        $this->enableBankTransfer();

        $user = $this->provider();

        $this->actingAs($user)->post(route('account.subscription.checkout', $this->plan()), [
            'payment_method' => PaymentMethod::CODE_BANK_TRANSFER,
        ]);

        $response = $this->actingAs($user)->get(route('account.subscription.index'));

        $response->assertSee('123456789/0100');
        $response->assertSee(Subscription::first()->payment_reference);
        $response->assertSee(__('front.payments.awaiting_transfer'));
    }

    /** Potvrzení je ten okamžik, kdy člověk ověřil, že peníze dorazily. */
    public function test_confirming_the_payment_activates_the_subscription(): void
    {
        $this->enableBankTransfer();

        $user = $this->provider();
        $plan = $this->plan();

        $this->actingAs($user)->post(route('account.subscription.checkout', $plan), [
            'payment_method' => PaymentMethod::CODE_BANK_TRANSFER,
        ]);

        $subscription = Subscription::first();
        $admin = User::factory()->create();

        app(BankTransfer::class)->confirm($subscription, $admin->id);

        $subscription->refresh();

        $this->assertSame(Subscription::STATUS_ACTIVE, $subscription->status);
        $this->assertNotNull($subscription->paid_at);
        $this->assertSame($admin->id, $subscription->payment_confirmed_by);
    }

    /**
     * Platnost se počítá od potvrzení, ne od objednávky. Kdo zaplatil o tři
     * dny později, má dostat celý měsíc.
     */
    public function test_the_period_starts_when_the_money_arrives(): void
    {
        $this->enableBankTransfer();

        $user = $this->provider();

        $this->actingAs($user)->post(route('account.subscription.checkout', $this->plan()), [
            'payment_method' => PaymentMethod::CODE_BANK_TRANSFER,
        ]);

        $subscription = Subscription::first();
        $subscription->forceFill(['created_at' => now()->subDays(3)])->save();

        app(BankTransfer::class)->confirm($subscription->fresh());

        $subscription->refresh();

        $this->assertTrue($subscription->starts_at->isToday());
        $this->assertSame(30, (int) $subscription->starts_at->diffInDays($subscription->ends_at));
    }

    /** Dokud nikdo nepotvrdil, objednávka na potvrzení čeká. */
    public function test_an_unconfirmed_order_is_reported_as_awaiting(): void
    {
        $this->enableBankTransfer();

        $this->actingAs($this->provider())->post(route('account.subscription.checkout', $this->plan()), [
            'payment_method' => PaymentMethod::CODE_BANK_TRANSFER,
        ]);

        $this->assertTrue(app(BankTransfer::class)->isAwaitingPayment(Subscription::first()));
    }

    /** Klíče Stripe zadané v administraci mají přednost před souborem. */
    public function test_stripe_keys_from_the_admin_win(): void
    {
        config(['services.stripe.secret' => 'sk_from_env']);

        $stripe = PaymentMethods::find(PaymentMethod::CODE_STRIPE);
        $stripe->forceFill([
            'is_enabled' => true,
            'settings' => ['secret_key' => 'sk_from_admin'],
        ])->save();

        $this->assertSame('sk_from_admin', \App\Services\Payments\StripeGateway::secret());
    }

    /** Bez klíče v administraci platí soubor — je to výchozí nastavení nasazení. */
    public function test_the_environment_stays_the_fallback(): void
    {
        config(['services.stripe.secret' => 'sk_from_env']);

        $this->assertSame('sk_from_env', \App\Services\Payments\StripeGateway::secret());
    }
}
