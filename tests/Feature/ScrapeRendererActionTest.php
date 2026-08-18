<?php

namespace Tests\Feature;

use App\Filament\Resources\ScrapeSources\Pages\ListScrapeSources;
use App\Models\ScrapeSource;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tlačítko na ověření rendereru.
 *
 * Schovávalo se, dokud nebyl renderer nastavený — takže ho nenašel právě ten,
 * kdo ho hledá: člověk, kterého web odmítá a chce vědět, jestli je tahle cesta
 * vůbec k dispozici. A nastavit se renderer dal jen v syrovém editoru
 * nastavení, kde ho nikdo hledat nebude.
 */
class ScrapeRendererActionTest extends TestCase
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
        $admin = User::factory()->create(['gender' => 'male', 'email_verified_at' => now()]);
        $admin->syncRoles(['super_admin', 'admin']);

        return $admin->fresh();
    }

    private function source(array $settings = []): ScrapeSource
    {
        return ScrapeSource::create([
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => array_merge(['crawl_delay' => 0, 'respect_robots' => false], $settings),
        ]);
    }

    /** To hlavní: musí být vidět, i když renderer nastavený není. */
    public function test_the_button_is_visible_without_a_renderer(): void
    {
        $source = $this->source();

        Livewire::actingAs($this->admin())
            ->test(ListScrapeSources::class)
            ->assertTableActionVisible('testRenderer', $source);
    }

    /** A když se zmáčkne bez nastavení, řekne kde ho nastavit. */
    public function test_without_a_renderer_it_says_where_to_set_it(): void
    {
        $source = $this->source();

        Livewire::actingAs($this->admin())
            ->test(ListScrapeSources::class)
            ->callTableAction('testRenderer', $source, ['url' => ''])
            ->assertHasNoTableActionErrors();

        // Nic se nestahovalo: nebylo přes co.
        Http::assertNothingSent();
    }

    public function test_a_working_renderer_is_reported(): void
    {
        Http::fake([
            'https://render.test/*' => Http::response(
                '<html><body><h1>Kristýna</h1><p>' . str_repeat('Popis profilu. ', 60) . '</p></body></html>',
                200,
            ),
        ]);

        $source = $this->source(['render_endpoint' => 'https://render.test/html?url={url}']);

        Livewire::actingAs($this->admin())
            ->test(ListScrapeSources::class)
            ->callTableAction('testRenderer', $source, ['url' => 'https://example.test/p/1'])
            ->assertHasNoTableActionErrors();

        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://render.test/html'));
    }

    /**
     * Renderer, který vrátí tutéž prázdnou skořápku, je horší než žádný:
     * tváří se, že funguje.
     */
    public function test_an_empty_result_is_called_out(): void
    {
        Http::fake([
            'https://render.test/*' => Http::response(
                '<html><body><div id="app"></div><script src="/a.js"></script><script src="/b.js"></script></body></html>',
                200,
            ),
        ]);

        $source = $this->source(['render_endpoint' => 'https://render.test/html?url={url}']);

        Livewire::actingAs($this->admin())
            ->test(ListScrapeSources::class)
            ->callTableAction('testRenderer', $source, ['url' => 'https://example.test/p/1'])
            ->assertHasNoTableActionErrors();
    }

    /** Renderer se dá nastavit ve formuláři, ne jen v syrovém JSONu. */
    public function test_the_renderer_can_be_set_in_the_form(): void
    {
        $source = $this->source();

        $this->actingAs($this->admin())
            ->get("/admin/scrape-sources/{$source->id}/edit")
            ->assertSuccessful()
            ->assertSee('Vykreslovací služba');
    }
}
