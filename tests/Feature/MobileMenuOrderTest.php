<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The mobile menu of a signed-in visitor.
 *
 * The design (`menu logged-in muž`) opens with Můj profil, Moje zprávy, Moje
 * favoritky, Základní nastavení. We had Základní nastavení first and the rest
 * in a different order — the four items were there, just not as drawn.
 *
 * The sections the design does not draw stay below them: on a phone there is
 * no other way into Archiv dívek, Dívky měsíce or Nahlášené dívky.
 */
class MobileMenuOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    /**
     * Targets of the account rows, in the order the mobile menu renders them.
     *
     * Matched on the link target rather than the label: the label sits after an
     * inline icon, and the icon markup differs per row.
     *
     * @return array<int, string>
     */
    private function accountRowOrder(string $html): array
    {
        $start = strpos($html, 'id="mobile-menu"');

        if ($start === false) {
            return [];
        }

        // Longest alternatives first: `account/member` must not be eaten by the
        // bare `account` branch.
        preg_match_all('~href="[^"]*?/(account/member/[a-z-]+|account/member|notifications/archived|messages|account)"~', substr($html, $start), $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    public function test_a_member_sees_the_designs_four_items_first(): void
    {
        $member = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);

        $order = $this->accountRowOrder($this->actingAs($member)->get('/')->getContent());

        $this->assertSame(
            [
                'account/member/ratings',   // Můj profil
                'messages',                 // Moje zprávy
                'account/member/favorites',// Moje favoritky
                'account/member',           // Základní nastavení
            ],
            array_slice($order, 0, 4),
        );
    }

    public function test_the_sections_the_design_omits_are_still_reachable(): void
    {
        $member = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);

        $html = $this->actingAs($member)->get('/')->getContent();

        foreach (['girls-of-month', 'archive', 'reported'] as $route) {
            $this->assertStringContainsString(
                'account/member/' . $route,
                $html,
                "Sekce {$route} není z mobilu dosažitelná.",
            );
        }
    }

    public function test_a_guest_sees_the_public_menu_and_the_languages(): void
    {
        $html = $this->get('/')->getContent();

        foreach (['Registrace', 'Log-in', 'Česky', 'English', 'Русский'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
    }
}
