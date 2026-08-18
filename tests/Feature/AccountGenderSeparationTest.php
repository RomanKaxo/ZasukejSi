<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Muž a žena mají v účtu jiné rozhraní.
 *
 * Ženě patří inzerát: fotky, služby, ceny, statistiky, recenze. Muži patří to,
 * co si o inzerátech myslí: hodnocení, oblíbené, archiv. Předplatné mají oba —
 * jenom každý jiné.
 *
 * Testuje se to výčtem adres, protože jediné, co „nedostupné" znamená, je že
 * na tu adresu nejde. Zbytek — odkaz v menu, tlačítko na stránce — se dá
 * přehlédnout při každé úpravě šablony.
 */
class AccountGenderSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
        $this->seed(RoleSeeder::class);
    }

    private function man(): User
    {
        return User::factory()->create([
            'gender' => 'male',
            'email_verified_at' => now(),
        ]);
    }

    private function woman(): User
    {
        $user = User::factory()->create([
            'gender' => 'female',
            'email_verified_at' => now(),
        ]);

        Profile::factory()->create(['user_id' => $user->id]);

        return $user->fresh();
    }

    /** @return array<string, array{0: string}> */
    public static function womenOnlyPages(): array
    {
        return [
            'fotografie' => ['/account/photos'],
            'služby' => ['/account/services'],
            'statistiky' => ['/account/statistics'],
            'recenze' => ['/account/reviews'],
            'předplatné inzerátu' => ['/account/subscription'],
        ];
    }

    /** @dataProvider womenOnlyPages */
    public function test_a_man_cannot_open_a_womens_page(string $path): void
    {
        $response = $this->actingAs($this->man())->get($path);

        $response->assertRedirect();
        $this->assertStringContainsString('/account/member', $response->headers->get('Location') ?? '');
    }

    /** @dataProvider womenOnlyPages */
    public function test_a_woman_can_open_her_own_pages(string $path): void
    {
        $this->actingAs($this->woman())->get($path)->assertSuccessful();
    }

    /** @return array<string, array{0: string}> */
    public static function menOnlyPages(): array
    {
        return [
            'hodnocení' => ['/account/member/ratings'],
            'oblíbené' => ['/account/member/favorites'],
            'dívky měsíce' => ['/account/member/girls-of-month'],
            'archiv' => ['/account/member/archive'],
            'nahlášené' => ['/account/member/reported'],
            'členství' => ['/account/member/membership'],
        ];
    }

    /** @dataProvider menOnlyPages */
    public function test_a_woman_cannot_open_a_mens_page(string $path): void
    {
        $response = $this->actingAs($this->woman())->get($path);

        $response->assertRedirect();
        $this->assertStringNotContainsString('/account/member', $response->headers->get('Location') ?? '');
    }

    /** @dataProvider menOnlyPages */
    public function test_a_man_can_open_his_own_pages(string $path): void
    {
        $this->actingAs($this->man())->get($path)->assertSuccessful();
    }

    /** Nastavení účtu a hesla patří oběma — nejsou to stránky o inzerátu. */
    public function test_shared_pages_are_open_to_both(): void
    {
        foreach (['/account/profile', '/account/password'] as $path) {
            $this->actingAs($this->man())->get($path)->assertSuccessful();
            $this->actingAs($this->woman())->get($path)->assertSuccessful();
        }
    }

    /**
     * Předplatné má být v menu i pro muže.
     *
     * Zelené tlačítko dole v postranním panelu bylo jediná cesta k členství,
     * takže položka, kterou muž hledá nejčastěji, v seznamu nebyla.
     */
    public function test_a_man_has_membership_in_his_sidebar(): void
    {
        $response = $this->actingAs($this->man())->get('/account/member/ratings');

        $response->assertSuccessful();
        $response->assertSee(route('account.member.membership.index'), false);
        $response->assertSee(__('front.account.member.membership'));
    }

    /** Menu v hlavičce nesmí muži nabízet ženské stránky. */
    public function test_the_header_menu_offers_a_man_only_his_own_pages(): void
    {
        $response = $this->actingAs($this->man())->get('/account/member/ratings');

        $response->assertDontSee(route('account.photos'), false);
        $response->assertDontSee(route('account.services'), false);
        $response->assertDontSee(route('account.statistics'), false);
    }

    /**
     * Účet bez vyplněného pohlaví nesmí spadnout do ženské větve jen proto, že
     * „není muž". Inzerát nemá a stránky o inzerátu mu k ničemu nejsou.
     */
    public function test_an_account_without_a_gender_is_not_treated_as_a_woman(): void
    {
        $user = User::factory()->create(['gender' => null, 'email_verified_at' => now()]);

        $this->actingAs($user)->get('/account/photos')->assertRedirect();
    }
}
