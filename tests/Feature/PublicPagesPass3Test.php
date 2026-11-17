<?php

namespace Tests\Feature;

use App\Livewire\ProfileList;
use App\Models\Page;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pass 3 (functional) of the public pages found:
 *
 * - every public page carried several <h1>s: the login, registration and
 *   password modals included on every page each had one, the homepage list
 *   heading added a second visible one under the hero title, and CMS pages
 *   printed the page title plus the editor's own "H1" heading block;
 * - /login and /register had no <h1> at all (their title was an <h2>);
 * - the pagination arrows and the photo dots on profile cards were buttons
 *   with no accessible name;
 * - a blog post's header image started 80 px from the top, under the 96 /
 *   100 px fixed header.
 */
class PublicPagesPass3Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('cs');
    }

    private function publicProfile(array $attributes = []): Profile
    {
        return Profile::factory()->for(User::factory())->create(array_merge([
            'status' => 'approved',
            'is_public' => true,
        ], $attributes));
    }

    private function h1Count(string $html): int
    {
        return preg_match_all('/<h1[\s>]/i', $html);
    }

    public function test_the_homepage_has_exactly_one_h1(): void
    {
        $this->publicProfile();

        foreach (['cs', 'en'] as $locale) {
            $html = $this->get('/?locale=' . $locale)->assertOk()->getContent();
            $this->assertSame(1, $this->h1Count($html), "homepage ($locale)");
        }
    }

    public function test_the_countries_listing_has_one_h1_per_viewport(): void
    {
        $this->publicProfile();

        $html = $this->get('/countries?locale=cs')->assertOk()->getContent();

        // The list heading exists twice, a desktop and a phone variant, and
        // CSS shows exactly one of them per viewport; nothing else is an h1.
        $this->assertSame(2, $this->h1Count($html));
        $this->assertMatchesRegularExpression('/<h1 class="[^"]*pl-heading__title/', $html);
        $this->assertMatchesRegularExpression('/<h1 class="mobile-top-results-title/', $html);
    }

    public function test_the_login_and_register_pages_have_exactly_one_h1(): void
    {
        foreach (['/login', '/register'] as $url) {
            $html = $this->get($url . '?locale=cs')->assertOk()->getContent();
            $this->assertSame(1, $this->h1Count($html), $url);
        }
    }

    public function test_a_cms_page_with_an_h1_block_still_has_one_h1(): void
    {
        Page::create([
            'title' => ['cs' => 'FAQ'],
            'slug' => 'faq',
            'type' => 'page',
            'is_published' => true,
            'content' => ['cs' => [[
                'type' => \SkyRaptor\FilamentBlocksBuilder\Blocks\Typography\Heading::class,
                'data' => ['content' => 'Často kladené otázky', 'level' => 'h1'],
            ]]],
        ]);

        $html = $this->get('/faq?locale=cs')->assertOk()->getContent();

        $this->assertSame(1, $this->h1Count($html));
        // The editor's heading is still shown, at the same H1 size.
        $this->assertMatchesRegularExpression('/<h2 class="[^"]*text-3xl[^"]*">Často kladené otázky<\/h2>/', $html);
    }

    public function test_pagination_arrows_have_an_accessible_name(): void
    {
        foreach (range(1, 26) as $i) {
            $this->publicProfile();
        }

        Livewire::test(ProfileList::class)
            ->assertSeeHtml('aria-label="' . __('front.profiles.list.a11y_prev_page') . '"')
            ->assertSeeHtml('aria-label="' . __('front.profiles.list.a11y_next_page') . '"');
    }

    public function test_card_photo_dots_and_arrows_have_an_accessible_name(): void
    {
        $profile = $this->publicProfile();

        $html = (string) $this->blade(
            '<x-profile-card :profile="$profile" :images-override="$images" />',
            ['profile' => $profile, 'images' => ['/a.jpg', '/b.jpg', '/c.jpg']]
        );

        foreach (['a11y_prev_photo', 'a11y_next_photo'] as $key) {
            $this->assertStringContainsString('aria-label="' . __('front.profiles.list.' . $key) . '"', $html);
        }
        foreach ([1, 2, 3] as $number) {
            $this->assertStringContainsString('aria-label="' . __('front.profiles.list.a11y_photo', ['number' => $number]) . '"', $html);
        }
    }

    /**
     * Phones have no hover arrows on the card; swiping the photo did nothing.
     * Every card photo was also fetched eagerly (5 per card × 25 cards).
     */
    public function test_card_photos_can_be_swiped_and_load_lazily(): void
    {
        $profile = $this->publicProfile();

        $html = (string) $this->blade(
            '<x-profile-card :profile="$profile" :images-override="$images" />',
            ['profile' => $profile, 'images' => ['/a.jpg', '/b.jpg']]
        );

        $this->assertStringContainsString('@touchstart.passive=', $html);
        $this->assertStringContainsString('@touchend=', $html);
        $this->assertSame(2, substr_count($html, 'loading="lazy"'));

        // One photo: nothing to swipe between.
        $single = (string) $this->blade(
            '<x-profile-card :profile="$profile" :images-override="$images" />',
            ['profile' => $profile, 'images' => ['/a.jpg']]
        );
        $this->assertStringNotContainsString('@touchend=', $single);
    }

    public function test_a_blog_post_header_image_clears_the_fixed_header(): void
    {
        Page::create([
            'title' => ['cs' => 'Bezpečnostní tipy'],
            'slug' => 'safety-tips',
            'type' => 'blog',
            'is_published' => true,
            'content' => ['cs' => []],
        ]);

        $html = $this->get('/safety-tips?locale=cs')->assertOk()->getContent();

        // 96 px below 1024 px, 100 px from 1024 px — the header's own heights.
        $this->assertStringContainsString('class="w-full mt-24 lg:mt-[100px]"', $html);
        $this->assertStringNotContainsString('class="w-full mt-20"', $html);
    }

    /**
     * /countries?region=… titles the list with the region. "zrušit filtr"
     * cleared the region and listed every profile, but the heading kept
     * saying "Moravskoslezský kraj".
     */
    public function test_resetting_filters_drops_the_region_heading(): void
    {
        $this->publicProfile();

        foreach (['resetFilters', 'clearLocation'] as $action) {
            Livewire::withQueryParams(['region' => 'Moravskoslezský Kraj'])
                ->test(ProfileList::class, ['listTitle' => 'Moravskoslezský kraj'])
                ->assertSee('Moravskoslezský kraj')
                ->call($action)
                ->assertSet('region', '')
                ->assertSet('listTitle', null)
                ->assertDontSee('Moravskoslezský kraj')
                ->assertSee(__('front.profiles.list.topresults'));
        }
    }

    /**
     * EN sidebar (vybraná země.jpg): a country with towns only toggled its
     * town list – a script cancelled the link, so the country could never be
     * selected – and the town list of the selected country stayed hidden.
     */
    public function test_the_english_sidebar_selects_a_country_and_lists_its_towns(): void
    {
        \App\Models\Country::create(['code' => 'CZ', 'sort_order' => 10]);
        \App\Models\City::create(['name' => 'Brno', 'name_ascii' => 'Brno', 'country_code' => 'CZ', 'admin_name' => 'Jihomoravský Kraj']);
        $this->publicProfile(['country_code' => 'CZ', 'city' => 'Brno', 'verified_at' => now()]);
        \App\Services\CountryStatsService::flush();

        $home = $this->get('/?locale=en')->assertOk()->getContent();
        $this->assertStringNotContainsString('a[data-has-regions]', $home, 'no script may cancel the country link');
        $this->assertMatchesRegularExpression('/<div data-regions data-country="cz"\s+style="display:none;"/', $home);

        $selected = $this->get('/?locale=en&country=Czechia&country_code=CZ')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<div data-regions data-country="cz"\s+class=/', $selected);
        $this->assertStringContainsString('Jihomoravský Kraj', $selected);
    }

    /**
     * EN location search: Livewire 3.8's mergeQueuedUpdates() drops every
     * other queued update of a request when one queued value is '' (it checks
     * keys against the value, and '' prefixes every key). selectCountry()
     * queued country, country_code and then region = '', so the server never
     * got the country: picking Germany searched Albania, the default first
     * country, and clearing the country kept it. The values are now sent
     * without `live` (as a plain diff) and committed once.
     *
     * The phone pickers also listed every country / town twice: a "server
     * fallback" list inside the x-teleport template, which ignored the search.
     */
    public function test_the_english_location_search_sends_the_picked_country(): void
    {
        $html = $this->get('/?locale=en')->assertOk()->getContent();

        $this->assertStringContainsString("\$wire.set('country', country.name, false);", $html);
        $this->assertStringContainsString("\$wire.set('country_code', country.code, false);", $html);
        $this->assertDoesNotMatchRegularExpression("/\\\$wire\\.set\\('(country|country_code|region)', ''\\)/", $html);
        $this->assertStringNotContainsString('search-country-mobile-picker__list-server', $html);
    }

    public function test_the_mobile_menu_button_has_an_accessible_name(): void
    {
        $html = $this->get('/?locale=cs')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/id="mobile-menu-button" aria-label="' . preg_quote(__('front.nav.menu_toggle'), '/') . '"/',
            $html
        );
    }

    /**
     * The homepage hero loaded a 3.6 MB PNG on desktop and a 1.1 MB PNG on
     * phones; both are now visually identical JPEGs of a few hundred KB.
     */
    public function test_the_hero_backgrounds_are_lightweight(): void
    {
        foreach (range(1, 11) as $i) {
            $this->publicProfile();
        }

        $html = $this->get('/?locale=cs')->assertOk()->getContent();

        $this->assertStringContainsString('/images/header.jpg', $html);
        $this->assertStringContainsString('/images/icons/mobile/mobile.jpg', $html);
        $this->assertStringNotContainsString('/images/header.png', $html);
        $this->assertStringNotContainsString('/images/icons/mobile/mobile.png', $html);

        // The advert block has a desktop and a phone picture, one of them
        // always hidden: both lazy, so the hidden one is never downloaded.
        $this->assertMatchesRegularExpression('#images/dvert\.png"\s+alt="[^"]*"\s+loading="lazy"#', $html);
        $this->assertMatchesRegularExpression('#images/icons/mobile/mobile2\.png"\s+alt="[^"]*"\s+loading="lazy"#', $html);

        foreach (['images/header.jpg', 'images/icons/mobile/mobile.jpg'] as $file) {
            $this->assertFileExists(public_path($file));
            $this->assertLessThan(500 * 1024, filesize(public_path($file)), $file);
        }
    }
}
