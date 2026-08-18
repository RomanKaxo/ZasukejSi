<?php

namespace Tests\Feature;

use App\Models\MemberSubscription;
use App\Models\PaymentMethod;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\User;
use App\Services\Payments\PaymentMethods;
use App\Support\OfflineCheckout;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kam se člověk dostane po koupi a co se mu tam řekne.
 *
 * Všechny cesty členství končily na nástěnce člena, která se v menu jmenuje
 * „Základní nastavení" — takže kdo si právě koupil Premium, se ocitl v
 * nastavení hesla. A zpráva se navíc nezobrazila vůbec: nástěnka vypisovala
 * jen „nastavení uloženo" a „heslo změněno", takže všechno ostatní zmizelo.
 *
 * Člověk tedy zaplatil a nedostal ani potvrzení, ani stránku, na které by si
 * ho mohl přečíst.
 */
class CheckoutRedirectsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
        $this->seed(RoleSeeder::class);

        PaymentMethods::sync();

        // Bez brány se objednávka dokončí bez placení — tím se dá projít celá
        // cesta až na cílovou stránku.
        \App\Models\Setting::set(OfflineCheckout::KEY, '1');
    }

    private function plan(string $audience): SubscriptionType
    {
        return SubscriptionType::create([
            'name' => ['cs' => $audience === 'member' ? 'Premium' : 'VIP', 'en' => 'Plan'],
            'slug' => $audience === 'member' ? 'premium' : 'vip',
            'audience' => $audience,
            'price' => 500,
            'price_czk' => 500,
            'duration_days' => 30,
            'is_active' => true,
        ]);
    }

    private function member(): User
    {
        return User::factory()->create(['gender' => 'male', 'email_verified_at' => now()])->fresh();
    }

    private function provider(): User
    {
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);
        Profile::factory()->create(['user_id' => $user->id]);

        return $user->fresh();
    }

    /** To hlavní: po koupi členství se jde na potvrzení, ne do nastavení. */
    public function test_buying_membership_lands_on_the_success_page(): void
    {
        $response = $this->actingAs($this->member())
            ->post(route('account.member.membership.checkout', $this->plan('member')));

        $response->assertRedirect(route('account.member.membership.success', ['granted' => 1]));

        $this->assertSame(1, MemberSubscription::count());
    }

    /**
     * A na té stránce se dočte, co se stalo — včetně toho, že se neplatilo.
     * Bliknutí, které zmizí při prvním kliknutí, na tohle nestačí.
     */
    public function test_the_success_page_says_what_happened(): void
    {
        $user = $this->member();

        $this->actingAs($user)
            ->followingRedirects()
            ->post(route('account.member.membership.checkout', $this->plan('member')))
            ->assertSuccessful()
            ->assertSee(__('front.membership.granted_title'))
            ->assertSee(__('front.membership.activated_without_payment'))
            ->assertSee(__('front.membership.back_to_membership'));
    }

    /** Prodloužení je tatáž cesta a musí skončit stejně. */
    public function test_extending_membership_lands_on_the_success_page(): void
    {
        $user = $this->member();
        $plan = $this->plan('member');

        // První nákup.
        $this->actingAs($user)->post(route('account.member.membership.checkout', $plan));

        // Prodloužení.
        $this->actingAs($user)
            ->post(route('account.member.membership.checkout', $plan))
            ->assertRedirect(route('account.member.membership.success', ['granted' => 1]));
    }

    /** Platba, kterou se nepodařilo ověřit, se nesmí tvářit jako úspěch. */
    public function test_an_unverified_return_says_so(): void
    {
        $this->actingAs($this->member())
            ->get(route('account.member.membership.success'))
            ->assertSuccessful()
            ->assertSee(__('front.membership.unverified_title'));
    }

    public function test_cancelling_membership_returns_to_the_membership_page(): void
    {
        $this->actingAs($this->member())
            ->get(route('account.member.membership.cancel'))
            ->assertRedirect(route('account.member.membership.index'));
    }

    /** U inzerátu totéž: dvě cesty ke stejnému výsledku končí stejně. */
    public function test_buying_vip_lands_on_the_success_page(): void
    {
        $response = $this->actingAs($this->provider())
            ->post(route('account.subscription.checkout', $this->plan('profile')));

        $response->assertRedirect(route('account.subscription.success', ['granted' => 1]));

        $this->assertSame(1, Subscription::count());
    }

    public function test_the_vip_success_page_says_what_happened(): void
    {
        $this->actingAs($this->provider())
            ->followingRedirects()
            ->post(route('account.subscription.checkout', $this->plan('profile')))
            ->assertSuccessful()
            ->assertSee(__('front.subscription.success_title'));
    }

    /**
     * Bankovní převod: zpráva musí být pod klíčem, který stránka vypisuje.
     * S jiným se ztratila a objednávka vypadala, že se nestala.
     */
    public function test_a_bank_transfer_order_says_so_on_the_subscription_page(): void
    {
        PaymentMethods::find(PaymentMethod::CODE_BANK_TRANSFER)->forceFill([
            'is_enabled' => true,
            'settings' => ['account_number' => '123456789/0100'],
        ])->save();

        $this->actingAs($this->provider())
            ->post(route('account.subscription.checkout', $this->plan('profile')), [
                'payment_method' => PaymentMethod::CODE_BANK_TRANSFER,
            ])
            ->assertRedirect(route('account.subscription.index'))
            ->assertSessionHas('status', __('front.payments.transfer_created'));
    }

    public function test_a_bank_transfer_membership_says_so_too(): void
    {
        PaymentMethods::find(PaymentMethod::CODE_BANK_TRANSFER)->forceFill([
            'is_enabled' => true,
            'settings' => ['account_number' => '123456789/0100'],
        ])->save();

        $this->actingAs($this->member())
            ->post(route('account.member.membership.checkout', $this->plan('member')), [
                'payment_method' => PaymentMethod::CODE_BANK_TRANSFER,
            ])
            ->assertRedirect(route('account.member.membership.index'))
            ->assertSessionHas('status', __('front.payments.transfer_created'));
    }

    /**
     * Nástěnka člena zahazovala všechno kromě dvou konkrétních hlášek.
     * Stránka, které se pošle zpráva, ji má ukázat.
     */
    public function test_the_member_dashboard_no_longer_swallows_messages(): void
    {
        $response = $this->actingAs($this->member())
            ->withSession(['status' => 'Členství je aktivní.'])
            ->get(route('account.member.dashboard'));

        $response->assertSuccessful();
        $response->assertSee('Členství je aktivní.');
    }
}
