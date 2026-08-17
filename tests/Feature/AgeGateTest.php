<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The 18+ gate.
 *
 * It was the one screen in the design that had no counterpart in the code at
 * all — no component, no middleware, not even a translation key. On a site
 * like this that is not a cosmetic gap, so it gets its own coverage: that it
 * stands, that it can be taken down, and that its wording is the operator's.
 */
class AgeGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::flush();
        Translation::flushCache();
    }

    public function test_the_gate_stands_by_default(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('id="age-gate"', false);
        $response->assertSee('Vítejte', false);
        $response->assertSee('Vstoupit', false);
        $response->assertSee('Odejít', false);
    }

    public function test_switching_it_off_leaves_it_out_of_the_page(): void
    {
        Setting::set('age_gate_enabled', '0');

        $response = $this->get('/');

        $response->assertOk();
        // Not hidden with CSS — absent, so nothing can reveal it.
        $response->assertDontSee('id="age-gate"', false);
    }

    public function test_switching_it_back_on_works(): void
    {
        Setting::set('age_gate_enabled', '0');
        Setting::set('age_gate_enabled', '1');

        $this->get('/')->assertSee('id="age-gate"', false);
    }

    public function test_the_wording_comes_from_the_admin(): void
    {
        Translation::updateOrCreate(
            ['locale' => 'cs', 'group' => 'front', 'key' => 'age_gate.heading'],
            ['value' => 'Pozor, 18+'],
        );
        Translation::flushCache();

        $this->get('/')->assertSee('Pozor, 18+', false);
    }

    /**
     * A changed text has to be acknowledged again, otherwise a visitor who
     * agreed to the old wording would never see the new one.
     */
    public function test_changing_the_text_changes_the_consent_version(): void
    {
        $before = $this->versionOnPage();

        Translation::updateOrCreate(
            ['locale' => 'cs', 'group' => 'front', 'key' => 'age_gate.body'],
            ['value' => 'Jiný právní text.'],
        );
        Translation::flushCache();

        $this->assertNotSame($before, $this->versionOnPage());
    }

    public function test_leaving_goes_where_the_admin_points_it(): void
    {
        Setting::set('age_gate_leave_url', 'https://example.test/pryc');

        $this->get('/')->assertSee('https://example.test/pryc', false);
    }

    /** The design's own text names a different company; ours must not. */
    public function test_the_default_text_does_not_name_another_company(): void
    {
        $this->get('/')->assertDontSee('Euro Girls Escort', false);
    }

    private function versionOnPage(): string
    {
        $html = $this->get('/')->getContent();

        preg_match('/id="age-gate" data-version="([^"]+)"/', $html, $m);

        return $m[1] ?? '';
    }
}
