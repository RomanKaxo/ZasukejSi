<?php

namespace Tests\Feature;

use App\Support\AdminGuides;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Legenda u každé sekce administrace.
 *
 * Většina obrazovek dává smysl, až když víte, co je za nimi: fronta scraperu
 * nepublikuje sama, hodnocení nejde přepsat, číselník vlastností řídí nabídku
 * ve dvou formulářích. To se dřív nedalo zjistit odnikud.
 */
class AdminSectionGuideTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
        $this->seed(RoleSeeder::class);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['email' => 'test@example.com']);
        $admin->syncRoles(['admin', 'super_admin']);

        return $admin;
    }

    public function test_the_guide_shows_on_a_section(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/profiles');

        $response->assertSuccessful();
        $response->assertSee('K čemu je tahle sekce');
        $response->assertSee('čeká na vaše schválení', false);
    }

    public function test_the_dashboard_has_one_too(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertSee('K čemu je tahle sekce');
    }

    /**
     * Zavřená, dokud si ji člověk sám neotevře.
     *
     * Legendu potřebuje jednou, ne při každém otevření sekce — otevřená by po
     * pár návštěvách byla jen řádek, který odsouvá tabulku dolů.
     */
    public function test_the_guide_starts_closed(): void
    {
        $html = $this->actingAs($this->admin())->get('/admin/profiles')->getContent();

        $this->assertStringContainsString('open: false', $html);
        $this->assertStringContainsString("localStorage.getItem('guide:profiles') === 'open'", $html);
    }

    /** Legenda popisuje sekci, ne obrazovku — platí i na detailu. */
    public function test_a_detail_screen_keeps_its_sections_guide(): void
    {
        $this->assertSame('profiles', AdminGuides::key('admin/profiles/12/edit'));
        $this->assertSame('profiles', AdminGuides::key('/admin/profiles'));
        $this->assertSame('', AdminGuides::key('/admin'));
    }

    public function test_every_admin_section_has_a_guide(): void
    {
        $missing = [];

        foreach (AdminPagesRenderTest::adminPages() as $label => [$url]) {
            if (AdminGuides::for($url) === null) {
                $missing[] = $label . ' (' . $url . ')';
            }
        }

        $this->assertSame([], $missing, 'Sekce bez legendy: ' . implode(', ', $missing));
    }

    public function test_the_links_point_at_sections_that_exist(): void
    {
        $known = array_keys(AdminGuides::all());
        $broken = [];

        foreach (AdminGuides::all() as $section => $guide) {
            foreach ($guide['links'] ?? [] as $label => $slug) {
                if (! in_array($slug, $known, true)) {
                    $broken[] = "{$section} → {$label} ({$slug})";
                }
            }
        }

        $this->assertSame([], $broken, 'Odkazy nikam: ' . implode(', ', $broken));
    }

    public function test_no_guide_is_only_an_intro_without_substance(): void
    {
        foreach (AdminGuides::all() as $section => $guide) {
            $this->assertArrayHasKey('intro', $guide, "Sekce {$section} nemá úvod.");
            $this->assertNotSame('', trim($guide['intro']), "Sekce {$section} má prázdný úvod.");
        }
    }
}
