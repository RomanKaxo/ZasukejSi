<?php

namespace Tests\Feature;

use App\Models\ScrapeSource;
use App\Services\Scraping\ConnectionDoctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Proč nás web odmítá.
 *
 * „HTTP 403 — web nás odmítl" je pravda a k ničemu. Neřekne, jestli web
 * odmítá adresu tohohle serveru, jméno našeho bota, nebo všechno, co nevypadá
 * jako prohlížeč — a každá z těch tří věcí se řeší jinak.
 *
 * Navíc odpověď závisí na tom, odkud se ptáte: tatáž adresa vrací 200 z
 * vývojářského stroje a 403 ze serveru. Hádat to lokálně je hádání.
 */
class ScrapeConnectionDoctorTest extends TestCase
{
    use RefreshDatabase;

    private function source(array $settings = []): ScrapeSource
    {
        return ScrapeSource::create([
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => array_merge([
                'crawl_delay' => 0,
                'respect_robots' => false,
                'user_agent' => 'ZasukejSiBot/1.0',
            ], $settings),
        ]);
    }

    /** Web pouští jen to, co vypadá jako prohlížeč. */
    public function test_it_finds_that_browser_headers_get_through(): void
    {
        Http::fake(function ($request) {
            $isBrowser = str_contains($request->header('User-Agent')[0] ?? '', 'Mozilla');

            return $isBrowser
                ? Http::response('<html><body>Profil</body></html>', 200)
                : Http::response('Access denied', 403);
        });

        $report = app(ConnectionDoctor::class)->diagnose($this->source());

        $this->assertFalse($report['attempts'][0]['ok'], 'Se současným nastavením to projít nemá.');
        $this->assertTrue($report['attempts'][1]['ok'], 'S hlavičkami prohlížeče ano.');
        $this->assertStringContainsString('Hlavičky jako prohlížeč', $report['verdict']);
    }

    /** A rovnou řekne, co uložit do nastavení. */
    public function test_it_hands_back_settings_to_save(): void
    {
        Http::fake(function ($request) {
            return str_contains($request->header('User-Agent')[0] ?? '', 'Mozilla')
                ? Http::response('<html>ok</html>', 200)
                : Http::response('Access denied', 403);
        });

        $report = app(ConnectionDoctor::class)->diagnose($this->source());

        $this->assertArrayHasKey('user_agent', $report['suggestion']);
        $this->assertStringContainsString('Mozilla', $report['suggestion']['user_agent']);

        // Hlavičky musí jít vložit do pole `headers` jako JSON.
        $headers = json_decode($report['suggestion']['headers'], true);

        $this->assertIsArray($headers);
        $this->assertArrayHasKey('Accept-Language', $headers);
        // User-Agent má vlastní pole; v hlavičkách by byl dvakrát.
        $this->assertArrayNotHasKey('User-Agent', $headers);
    }

    /** Cloudflare s kontrolou prohlížeče: žádné hlavičky nepomůžou a je to potřeba říct. */
    public function test_it_recognises_a_javascript_challenge(): void
    {
        Http::fake([
            '*' => Http::response(
                '<html><head><title>Just a moment...</title></head>'
                . '<body>Enable JavaScript and cookies to continue</body></html>',
                403,
                ['cf-ray' => 'abc123', 'Server' => 'cloudflare'],
            ),
        ]);

        $report = app(ConnectionDoctor::class)->diagnose($this->source());

        $this->assertNotNull($report['protection']);
        $this->assertStringContainsString('JavaScript', $report['verdict']);
        $this->assertStringContainsString('render_endpoint', $report['verdict']);
        $this->assertSame([], $report['suggestion'], 'Nemá co nabídnout, protože nic nefunguje.');
    }

    /** Když neprojde ani robots.txt, je zablokovaná adresa serveru. */
    public function test_it_blames_the_address_when_nothing_works(): void
    {
        Http::fake(['*' => Http::response('Forbidden', 403)]);

        $report = app(ConnectionDoctor::class)->diagnose($this->source());

        $this->assertStringContainsString('adresy tohohle serveru', $report['verdict']);
    }

    /** robots.txt projde, stránka ne — je to o cestě, ne o adrese. */
    public function test_it_distinguishes_a_blocked_path(): void
    {
        Http::fake([
            'https://example.test/robots.txt' => Http::response("User-agent: *\nDisallow:", 200),
            '*' => Http::response('Forbidden', 403),
        ]);

        $report = app(ConnectionDoctor::class)->diagnose($this->source(), 'https://example.test/eskort/brno');

        $this->assertStringContainsString('na té cestě', $report['verdict']);
    }

    /** Když web odpovídá normálně, nemá se nic vymýšlet. */
    public function test_it_says_plainly_when_there_is_no_problem(): void
    {
        Http::fake(['*' => Http::response('<html>ok</html>', 200)]);

        $report = app(ConnectionDoctor::class)->diagnose($this->source());

        $this->assertTrue($report['attempts'][0]['ok']);
        $this->assertStringContainsString('Blokace tady není', $report['verdict']);
        $this->assertSame([], $report['suggestion']);
    }

    /** Kousek těla odpovědi — podle něj se pozná, kdo odmítl. */
    public function test_the_refusal_body_is_reported(): void
    {
        Http::fake(['*' => Http::response('<html><body><h1>Access denied</h1><p>Ray ID 42</p></body></html>', 403)]);

        $report = app(ConnectionDoctor::class)->diagnose($this->source());

        $this->assertStringContainsString('Access denied', (string) $report['attempts'][0]['body']);
    }
}
