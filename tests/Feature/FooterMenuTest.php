<?php

namespace Tests\Feature;

use App\Models\FooterMenuItem;
use App\Models\Page;
use App\Models\Translation;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\FooterMenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The footer's links were CMS pages under their own titles, so it could not
 * carry a link worded differently from the page, two links pointing at one
 * page, or anything outside the CMS. The menu is its own thing now, arranged
 * the way the header's is.
 */
class FooterMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CurrencySeeder::class);
    }

    private function page(string $slug, string $title): Page
    {
        return Page::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => ['cs' => $title, 'en' => $title],
                'type' => 'page',
                'content' => ['cs' => [], 'en' => []],
                'is_published' => true,
                'display_in_footer' => true,
            ]
        );
    }

    /** @return array<int, array<int, string>> */
    private function renderedColumns(): array
    {
        $html = $this->get('/')->assertSuccessful()->getContent();

        preg_match('/<div class="footer-links">(.*?)<\/div>\s*<!-- Right: Security box -->/s', $html, $block);
        $this->assertNotEmpty($block, 'Blok odkazů v patičce se nenašel.');

        preg_match_all('/<div class="footer-col">(.*?)<\/div>/s', $block[1], $columns);

        return array_map(
            function (string $column) {
                preg_match_all('/class="footer-link"[^>]*>(.*?)<\/a>/s', $column, $links);

                return array_map('trim', $links[1]);
            },
            $columns[1]
        );
    }

    public function test_without_menu_items_the_footer_still_lists_pages(): void
    {
        $this->page('faq', 'FAQ');
        $this->page('kontakt', 'Kontakt');

        // Nothing must vanish on the deploy that introduces the menu.
        $this->assertSame([['FAQ'], ['Kontakt']], $this->renderedColumns());
    }

    public function test_a_menu_item_can_be_worded_differently_from_its_page(): void
    {
        $page = $this->page('faq', 'FAQ');

        FooterMenuItem::create([
            'label' => ['cs' => 'Časté dotazy', 'en' => 'FAQ'],
            'page_id' => $page->id,
            'column' => 1,
            'sort_order' => 10,
        ]);

        $columns = $this->renderedColumns();

        $this->assertSame([['Časté dotazy']], $columns);
    }

    public function test_two_items_can_point_at_one_page(): void
    {
        $page = $this->page('vip-premium', 'VIP & Premium');

        FooterMenuItem::create([
            'label' => ['cs' => 'VIP účet pro dívky'],
            'page_id' => $page->id,
            'column' => 3,
            'sort_order' => 10,
        ]);
        FooterMenuItem::create([
            'label' => ['cs' => 'Prémium účet pro pány'],
            'page_id' => $page->id,
            'column' => 3,
            'sort_order' => 20,
        ]);

        $this->assertSame([['VIP účet pro dívky', 'Prémium účet pro pány']], $this->renderedColumns());
    }

    public function test_an_item_can_point_outside_the_cms(): void
    {
        FooterMenuItem::create([
            'label' => ['cs' => 'Náš blog'],
            'url' => 'https://example.com/blog',
            'column' => 1,
            'sort_order' => 10,
            'opens_in_new_tab' => true,
        ]);

        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('https://example.com/blog', $html);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function test_columns_and_order_follow_the_admin(): void
    {
        foreach ([[1, 20, 'Druhá'], [1, 10, 'První'], [2, 10, 'Vedle']] as [$column, $order, $label]) {
            FooterMenuItem::create([
                'label' => ['cs' => $label],
                'url' => '/x',
                'column' => $column,
                'sort_order' => $order,
            ]);
        }

        $this->assertSame([['První', 'Druhá'], ['Vedle']], $this->renderedColumns());
    }

    public function test_a_hidden_item_is_not_rendered(): void
    {
        FooterMenuItem::create(['label' => ['cs' => 'Vidět'], 'url' => '/a', 'column' => 1, 'sort_order' => 10]);
        FooterMenuItem::create(['label' => ['cs' => 'Skryté'], 'url' => '/b', 'column' => 1, 'sort_order' => 20, 'is_visible' => false]);

        $this->assertSame([['Vidět']], $this->renderedColumns());
    }

    /**
     * A link into nothing is worse than one link fewer.
     */
    public function test_an_item_whose_page_is_unpublished_is_left_out(): void
    {
        $page = $this->page('faq', 'FAQ');

        FooterMenuItem::create(['label' => ['cs' => 'Časté dotazy'], 'page_id' => $page->id, 'column' => 1, 'sort_order' => 10]);
        FooterMenuItem::create(['label' => ['cs' => 'Kontakt'], 'url' => '/contact', 'column' => 1, 'sort_order' => 20]);

        $page->update(['is_published' => false]);

        $this->assertSame([['Kontakt']], $this->renderedColumns());
    }

    public function test_the_seeder_reproduces_the_current_footer(): void
    {
        $this->page('a', 'Áčko');
        $this->page('b', 'Béčko');
        $this->page('c', 'Céčko');

        $before = $this->renderedColumns();

        $this->seed(FooterMenuSeeder::class);

        $this->assertSame($before, $this->renderedColumns());
        $this->assertSame(3, FooterMenuItem::count());
    }

    public function test_the_seeder_never_overwrites_an_existing_arrangement(): void
    {
        FooterMenuItem::create(['label' => ['cs' => 'Moje'], 'url' => '/x', 'column' => 1, 'sort_order' => 10]);

        $this->page('faq', 'FAQ');
        $this->seed(FooterMenuSeeder::class);

        $this->assertSame(1, FooterMenuItem::count());
    }

    /**
     * The footer's wording was written into the template; it is editable now.
     */
    public function test_the_footer_texts_can_be_changed_in_the_admin(): void
    {
        Translation::updateOrCreate(
            ['locale' => 'cs', 'group' => 'front', 'key' => 'footer.copyright'],
            ['value' => '© 2026 Nový text']
        );
        Translation::updateOrCreate(
            ['locale' => 'cs', 'group' => 'front', 'key' => 'footer.logo_primary'],
            ['value' => 'JINÉ']
        );
        Translation::flushCache();

        $response = $this->get('/');

        $response->assertSee('© 2026 Nový text');
        $response->assertSee('JINÉ');
    }

    /**
     * The design lists a VIP plan for women and a Premium one for men side by
     * side, which only makes sense to somebody who is neither yet.
     */
    public function test_a_signed_in_visitor_is_offered_only_her_own_plan(): void
    {
        $page = $this->page('vip-premium', 'VIP & Premium');

        FooterMenuItem::create([
            'label' => ['cs' => 'VIP účet pro dívky'],
            'page_id' => $page->id,
            'audience' => FooterMenuItem::AUDIENCE_WOMEN,
            'column' => 1,
            'sort_order' => 10,
        ]);
        FooterMenuItem::create([
            'label' => ['cs' => 'Prémium účet pro pány'],
            'page_id' => $page->id,
            'audience' => FooterMenuItem::AUDIENCE_MEN,
            'column' => 1,
            'sort_order' => 20,
        ]);

        $woman = \App\Models\User::factory()->create(['gender' => 'female']);
        $man = \App\Models\User::factory()->create(['gender' => 'male']);

        $this->actingAs($woman);
        $this->assertSame([['VIP účet pro dívky']], $this->renderedColumns());

        $this->actingAs($man);
        $this->assertSame([['Prémium účet pro pány']], $this->renderedColumns());
    }

    public function test_a_visitor_who_is_not_signed_in_gets_the_general_page(): void
    {
        $page = $this->page('vip-premium', 'VIP & Premium');

        FooterMenuItem::create([
            'label' => ['cs' => 'VIP a Premium'],
            'page_id' => $page->id,
            'audience' => FooterMenuItem::AUDIENCE_GUESTS,
            'column' => 1,
            'sort_order' => 10,
        ]);
        FooterMenuItem::create([
            'label' => ['cs' => 'VIP účet pro dívky'],
            'page_id' => $page->id,
            'audience' => FooterMenuItem::AUDIENCE_WOMEN,
            'column' => 1,
            'sort_order' => 20,
        ]);

        // No role yet, so asking her or him to pick a side is meaningless.
        $this->assertSame([['VIP a Premium']], $this->renderedColumns());
    }

    public function test_an_item_for_everybody_shows_to_everybody(): void
    {
        FooterMenuItem::create([
            'label' => ['cs' => 'Kontakt'],
            'url' => '/contact',
            'column' => 1,
            'sort_order' => 10,
        ]);

        $this->assertSame([['Kontakt']], $this->renderedColumns());

        $this->actingAs(\App\Models\User::factory()->create(['gender' => 'male']));

        $this->assertSame([['Kontakt']], $this->renderedColumns());
    }

    /**
     * Terms of service belong in the footer; they were seeded there and must
     * stay there.
     */
    public function test_the_terms_of_service_link_is_seeded_into_the_footer(): void
    {
        $this->page('terms-of-service', 'Obchodní podmínky');
        $this->page('faq', 'FAQ');

        $this->seed(FooterMenuSeeder::class);

        $this->assertTrue(
            FooterMenuItem::query()->get()->contains(
                fn (FooterMenuItem $item) => $item->page?->slug === 'terms-of-service'
            )
        );
    }

    public function test_the_seeder_targets_the_two_plan_links(): void
    {
        $this->page('vip-premium', 'VIP & Premium');

        $this->seed(FooterMenuSeeder::class);

        $audiences = FooterMenuItem::query()
            ->whereHas('page', fn ($q) => $q->where('slug', 'vip-premium'))
            ->pluck('audience')
            ->sort()
            ->values()
            ->all();

        // The generic one is kept for guests so it does not say the same thing
        // three times to the same person.
        $this->assertSame(
            [FooterMenuItem::AUDIENCE_GUESTS, FooterMenuItem::AUDIENCE_MEN, FooterMenuItem::AUDIENCE_WOMEN],
            $audiences
        );
    }

    public function test_the_logo_keeps_its_three_coloured_parts(): void
    {
        $html = $this->get('/')->getContent();

        // Same markup as before, only the words moved out of the template.
        $this->assertStringContainsString('<span style="color:#5C2D62">ZAŠUKEJ</span>', $html);
        $this->assertStringContainsString('<span style="color:#DD3888">SI</span>', $html);
        $this->assertStringContainsString('.CZ</span>', $html);
    }
}
