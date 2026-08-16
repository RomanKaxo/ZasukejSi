<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Support\FooterButton;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The footer's links and its button are the admin's to arrange.
 *
 * The links were already CMS-driven but shared `sort_order` with the header, so
 * arranging one dragged the other along. The button was hardcoded.
 */
class FooterConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
        Setting::flush();
    }

    private function page(string $slug, string $title, array $attributes = []): Page
    {
        return Page::updateOrCreate(
            ['slug' => $slug],
            array_merge([
                'title' => ['cs' => $title, 'en' => $title],
                'type' => 'page',
                'content' => ['cs' => [], 'en' => []],
                'is_published' => true,
                'display_in_footer' => true,
            ], $attributes)
        );
    }

    public function test_the_footer_order_is_independent_of_the_menu(): void
    {
        $this->page('prvni-v-menu', 'První v menu', ['sort_order' => 10, 'footer_sort_order' => 30]);
        $this->page('druha', 'Druhá', ['sort_order' => 20, 'footer_sort_order' => 10]);
        $this->page('treti', 'Třetí', ['sort_order' => 30, 'footer_sort_order' => 20]);

        $footer = Page::where('display_in_footer', true)->footerOrdered()->pluck('slug')->all();
        $menu = Page::ordered()->pluck('slug')->all();

        $this->assertSame(['druha', 'treti', 'prvni-v-menu'], $footer);
        $this->assertSame(['prvni-v-menu', 'druha', 'treti'], $menu);
    }

    public function test_a_page_without_a_footer_order_follows_the_menu(): void
    {
        $this->page('prvni', 'První', ['sort_order' => 10]);
        $this->page('druha', 'Druhá', ['sort_order' => 20]);

        // Nothing set: switching a page into the footer must not need a second
        // number before it lands somewhere sensible.
        $this->assertSame(
            ['prvni', 'druha'],
            Page::where('display_in_footer', true)->footerOrdered()->pluck('slug')->all()
        );
    }

    public function test_the_footer_renders_the_admin_order(): void
    {
        $this->page('kontakt', 'Kontakt', ['sort_order' => 10, 'footer_sort_order' => 20]);
        $this->page('faq', 'Časté dotazy', ['sort_order' => 20, 'footer_sort_order' => 10]);

        $html = $this->get('/')->assertSuccessful()->getContent();

        // Scoped to the footer's own link block — "Kontakt" also appears in the
        // navigation further up the document.
        preg_match('/<div class="footer-links">(.*?)<\/div>\s*<!-- Right: Security box -->/s', $html, $matches);
        $this->assertNotEmpty($matches, 'Blok odkazů v patičce se nenašel.');

        $this->assertLessThan(
            strpos($matches[1], 'Kontakt'),
            strpos($matches[1], 'Časté dotazy')
        );
    }

    /**
     * The header carries its own "Registrace" button, so assertions about the
     * footer's one have to be scoped to the element itself.
     */
    private function footerButton(string $html): string
    {
        preg_match('/<(a|button)[^>]*footer-register.*?<\/\1>/s', $html, $matches);

        $this->assertNotEmpty($matches, 'Tlačítko v patičce se nevykreslilo.');

        return $matches[0];
    }

    public function test_a_guest_gets_the_registration_modal_by_default(): void
    {
        $button = $this->footerButton($this->get('/')->getContent());

        $this->assertStringContainsString(__('front.footer.registration'), $button);
        $this->assertStringContainsString('show-register-modal', $button);
    }

    public function test_a_signed_in_visitor_is_offered_vip_premium_by_default(): void
    {
        $page = $this->page('vip-premium', 'VIP & Premium');
        $user = User::factory()->create(['gender' => 'female']);

        $button = $this->footerButton($this->actingAs($user)->get('/')->getContent());

        $this->assertStringContainsString(url('/' . $page->slug), $button);
        $this->assertStringNotContainsString('show-register-modal', $button);
    }

    public function test_the_admin_can_point_the_button_at_any_page(): void
    {
        $this->page('vip-premium', 'VIP & Premium');
        $target = $this->page('etika', 'Etika a bezpečnost');

        Setting::set(FooterButton::KEY_AUTH_PAGE, $target->id);
        Setting::set(FooterButton::KEY_AUTH_LABEL, 'Naše pravidla');

        $user = User::factory()->create(['gender' => 'female']);
        $response = $this->actingAs($user)->get('/');

        $response->assertSee('Naše pravidla');
        $response->assertSee(url('/etika'), false);
    }

    public function test_an_empty_label_falls_back_to_the_page_title(): void
    {
        $target = $this->page('vip-premium', 'VIP & Premium');

        Setting::set(FooterButton::KEY_AUTH_PAGE, $target->id);
        Setting::set(FooterButton::KEY_AUTH_LABEL, '');

        $user = User::factory()->create(['gender' => 'female']);

        $this->actingAs($user)->get('/')->assertSee('VIP &amp; Premium', false);
    }

    public function test_the_guest_button_can_also_be_pointed_at_a_page(): void
    {
        $target = $this->page('vip-premium', 'VIP & Premium');

        Setting::set(FooterButton::KEY_GUEST_PAGE, $target->id);

        $button = $this->footerButton($this->get('/')->getContent());

        $this->assertStringContainsString(url('/vip-premium'), $button);
        $this->assertStringNotContainsString('show-register-modal', $button);
    }

    /**
     * A target that has been unpublished or deleted must not leave the slot
     * empty — the row is space-between, so an empty slot moves everything else.
     */
    public function test_an_unpublished_target_falls_back_instead_of_vanishing(): void
    {
        $target = $this->page('etika', 'Etika a bezpečnost');
        Setting::set(FooterButton::KEY_AUTH_PAGE, $target->id);
        Setting::set(FooterButton::KEY_GUEST_PAGE, $target->id);

        $target->update(['is_published' => false]);
        Setting::flush();

        $this->get('/')
            ->assertSee('footer-register', false)
            ->assertSee(__('front.footer.registration'));

        $user = User::factory()->create(['gender' => 'female']);

        $this->actingAs($user)->get('/')
            ->assertSee('footer-register', false)
            ->assertSee(route('account.dashboard'), false);
    }

    public function test_a_member_without_any_page_lands_on_his_own_dashboard(): void
    {
        $member = User::factory()->create(['gender' => 'male']);

        $this->actingAs($member)->get('/')
            ->assertSee(route('account.member.dashboard'), false);
    }
}
