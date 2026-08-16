<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The footer's left slot held a 141x55 button wrapped in `@guest`. For a
 * signed-in visitor the slot vanished, and because the row is
 * `justify-content: space-between` the links and the security box slid left —
 * so the footer stopped matching the design on every page behind a login.
 */
class FooterConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);

        Page::updateOrCreate(
            ['slug' => 'contact'],
            [
                'title' => ['cs' => 'Kontakt', 'en' => 'Contact'],
                'type' => 'page',
                'content' => ['cs' => [], 'en' => []],
                'is_published' => true,
                'display_in_footer' => true,
            ]
        );
    }

    public function test_a_guest_sees_the_registration_button(): void
    {
        $response = $this->get('/');

        $response->assertSuccessful();
        $response->assertSee('footer-register', false);
        $response->assertSee(__('front.footer.registration'));
    }

    public function test_a_signed_in_visitor_keeps_the_button_slot(): void
    {
        $provider = User::factory()->create(['gender' => 'female']);

        $response = $this->actingAs($provider)->get('/');

        $response->assertSuccessful();
        // Same class, so the same 141x55 box: the row must not collapse.
        $response->assertSee('footer-register', false);
        $response->assertSee(route('account.dashboard'), false);
    }

    public function test_a_member_is_sent_to_his_own_dashboard(): void
    {
        $member = User::factory()->create(['gender' => 'male']);

        $response = $this->actingAs($member)->get('/');

        $response->assertSuccessful();
        $response->assertSee('footer-register', false);
        $response->assertSee(route('account.member.dashboard'), false);
    }

    public static function pages(): array
    {
        return [
            'homepage' => ['/'],
            'zeme' => ['/countries'],
            'kontakt' => ['/contact'],
        ];
    }

    /**
     * The footer has to be whole on every page, not just the homepage.
     *
     * @dataProvider pages
     */
    public function test_the_footer_is_complete_on_every_page(string $url): void
    {
        $response = $this->get($url);

        $response->assertSuccessful();
        $response->assertSee('site-footer', false);
        $response->assertSee('footer-register', false);
        $response->assertSee('footer-security', false);
        $response->assertSee(__('front.footer.copyright'));
        $response->assertSee(__('front.footer.discreet'));
    }

    public function test_the_account_pages_carry_the_whole_footer(): void
    {
        $provider = User::factory()->create(['gender' => 'female']);
        Profile::factory()->create(['user_id' => $provider->id]);

        $response = $this->actingAs($provider)->get('/account');

        $response->assertSuccessful();
        $response->assertSee('footer-register', false);
        $response->assertSee('footer-security', false);
        $response->assertSee(__('front.footer.copyright'));
    }
}
