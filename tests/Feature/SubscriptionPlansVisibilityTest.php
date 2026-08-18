<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\SubscriptionType;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Komu se na stránce VIP & Premium co ukáže.
 *
 * Přihlášenému jen to, co může koupit — o jeho pohlaví rozhoduje účet, ne role.
 * Nepřihlášený vidí obojí a místo tlačítka „koupit" výzvu k přihlášení.
 */
class SubscriptionPlansVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seedPlans();
        $this->vipPage();
    }

    private function seedPlans(): void
    {
        foreach ([['profile', 'VIP'], ['member', 'Premium']] as [$audience, $name]) {
            SubscriptionType::create([
                'name' => ['cs' => $name, 'en' => $name],
                'slug' => strtolower($name),
                'audience' => $audience,
                'price' => 500,
                'price_czk' => 500,
                'duration_days' => 30,
                'is_active' => true,
            ]);
        }
    }

    private function vipPage(): void
    {
        Page::create([
            'title' => ['cs' => 'VIP & Premium', 'en' => 'VIP & Premium'],
            'slug' => 'vip-premium',
            'content' => [],
            'is_published' => true,
        ]);
    }

    public function test_a_guest_sees_both_sets_and_is_asked_to_sign_in(): void
    {
        $response = $this->get('/vip-premium');

        $response->assertSee(__('front.plans.for_women'));
        $response->assertSee(__('front.plans.for_men'));
        $response->assertSee(__('front.plans.sign_in_to_buy'));
    }

    public function test_a_woman_sees_only_her_own(): void
    {
        $user = User::factory()->create(['gender' => 'female', 'email_verified_at' => now()]);

        $response = $this->actingAs($user)->get('/vip-premium');

        $response->assertSee(__('front.plans.for_women'));
        $response->assertDontSee(__('front.plans.for_men'));
    }

    public function test_a_man_sees_only_his_own(): void
    {
        $user = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);

        $response = $this->actingAs($user)->get('/vip-premium');

        $response->assertSee(__('front.plans.for_men'));
        $response->assertDontSee(__('front.plans.for_women'));
    }

    /**
     * Administrátor má taky pohlaví.
     *
     * Byl z filtru vyňatý, aby si mohl stránku prohlédnout celou — jenže pak
     * viděl obojí a vypadalo to, že filtr nefunguje.
     */
    public function test_an_admin_is_filtered_like_anybody_else(): void
    {
        $admin = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
        $admin->syncRoles(['admin']);

        $response = $this->actingAs($admin->fresh())->get('/vip-premium');

        $response->assertSee(__('front.plans.for_men'));
        $response->assertDontSee(__('front.plans.for_women'));
    }

    /**
     * Administrátor je taky kupující.
     *
     * Skupiny se filtrovaly podle pohlaví, ale uvnitř byl administrátor z
     * nákupu vyňatý — takže u členství pro muže viděl místo tlačítka hlášku
     * „toto členství je určeno pro muže". O svém vlastním členství.
     */
    public function test_an_admin_gets_a_buy_button_not_a_notice(): void
    {
        $admin = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
        $admin->syncRoles(['admin']);

        $response = $this->actingAs($admin->fresh())->get('/vip-premium');

        $response->assertSee(__('front.plans.choose'));
        $response->assertDontSee(__('front.plans.men_only'));
    }

    /** Tarif se dá ze stránky sundat, aniž by se přestal prodávat z účtu. */
    public function test_a_plan_can_be_hidden_from_the_page(): void
    {
        $user = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);

        SubscriptionType::where('audience', 'member')->update(['show_on_plans_page' => false]);

        $response = $this->actingAs($user)->get('/vip-premium');

        // Ani „Vybrat", ani „Premium" se tvrdit nedá: obojí je na stránce
        // i mimo karty tarifů (nadpis stránky je „VIP & Premium"). To, co
        // schování tarifu opravdu znamená, je prázdná nabídka.
        $response->assertSee(__('front.membership.no_plans'));

        // Ale pořád je aktivní, takže se dá koupit odjinud.
        $this->assertTrue(SubscriptionType::where('audience', 'member')->first()->is_active);
    }

    /** Host má být pozvaný k přihlášení, ne k registraci — účet už mít může. */
    public function test_the_guest_button_opens_the_login_window(): void
    {
        $this->get('/vip-premium')->assertSee('show-login-modal', false);
    }
}
