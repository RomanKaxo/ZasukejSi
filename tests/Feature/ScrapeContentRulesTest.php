<?php

namespace Tests\Feature;

use App\Models\ScrapeItem;
use App\Models\ScrapeSource;
use App\Services\Scraping\ContentRules;
use App\Services\Scraping\ScrapeRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Pravidla, která si provozovatel napíše sám.
 *
 * Věková pojistka je v kódu, protože se o ní nediskutuje. Všechno ostatní, co
 * má zůstat venku — agenturní spam vlepený do každého popisu, město, které
 * nechceme — je úsudek o tomhle katalogu v tuhle chvíli a mění se. V kódu by
 * to znamenalo deploy při každé změně.
 */
class ScrapeContentRulesTest extends TestCase
{
    use RefreshDatabase;

    private function source(?string $rules): ScrapeSource
    {
        $source = ScrapeSource::create([
            'name' => 'Test',
            'slug' => 'test',
            'base_url' => 'https://example.test',
            'adapter' => 'generic',
            'is_enabled' => true,
            'settings' => [
                'crawl_delay' => 0,
                'listing_path' => '/list',
                'detail_link_selector' => 'a.profile',
                'respect_robots' => false,
                'conditional_requests' => false,
                'content_rules' => $rules,
            ],
        ]);

        foreach ([['display_name', 'h1'], ['city', '.mesto'], ['about_me', '.popis']] as $index => [$field, $selector]) {
            $source->fieldMaps()->create([
                'target_field' => $field,
                'selector' => $selector,
                'extract' => 'text',
                'sort_order' => $index,
            ]);
        }

        return $source;
    }

    private function fake(string $city = 'Brno', string $about = 'Milá společnice.'): void
    {
        Http::fake([
            'https://example.test/list/' => Http::response(
                '<a class="profile" href="https://example.test/p/1">A</a>',
                200,
                ['Content-Type' => 'text/html'],
            ),
            'https://example.test/p/1' => Http::response(
                '<h1>Kristýna</h1><div class="mesto">' . $city . '</div><div class="popis">' . $about . '</div>',
                200,
                ['Content-Type' => 'text/html'],
            ),
        ]);
    }

    public function test_a_matching_pattern_keeps_the_item_out(): void
    {
        $this->fake('Brno', 'Volejte na whatsapp +420111222333, jsme agentura.');

        app(ScrapeRunner::class)->run(
            $this->source('about_me ~ /whatsapp\s*\+\d{6,}/i'),
            ['pages' => 1],
        );

        $item = ScrapeItem::first();

        $this->assertSame(ScrapeItem::STATUS_REJECTED, $item->status);
        $this->assertStringContainsString('pravidlem zdroje', (string) $item->error);
    }

    public function test_an_item_that_breaks_no_rule_goes_through(): void
    {
        $this->fake('Brno', 'Milá společnice.');

        app(ScrapeRunner::class)->run(
            $this->source('about_me ~ /whatsapp/i'),
            ['pages' => 1],
        );

        $this->assertSame(ScrapeItem::STATUS_PENDING, ScrapeItem::first()->status);
    }

    public function test_a_city_can_be_required(): void
    {
        $this->fake('Ostrava');

        app(ScrapeRunner::class)->run($this->source('city != Brno'), ['pages' => 1]);

        $this->assertSame(ScrapeItem::STATUS_REJECTED, ScrapeItem::first()->status);
    }

    public function test_a_missing_field_can_be_the_rule(): void
    {
        $this->fake('Brno', '');

        app(ScrapeRunner::class)->run($this->source('about_me empty'), ['pages' => 1]);

        $this->assertSame(ScrapeItem::STATUS_REJECTED, ScrapeItem::first()->status);
    }

    /** Poznámky a prázdné řádky se přeskakují, ať se v pravidlech dá vyznat. */
    public function test_comments_and_blank_lines_are_ignored(): void
    {
        $this->fake('Brno', 'Milá společnice.');

        $rules = "# tohle je poznámka\n\ncity != Brno\n";

        app(ScrapeRunner::class)->run($this->source($rules), ['pages' => 1]);

        $this->assertSame(ScrapeItem::STATUS_PENDING, ScrapeItem::first()->status);
    }

    /**
     * Nesrozumitelný řádek se přeskočí a řekne se to. Pravidlo, kterému nikdo
     * nerozumí, nesmí potichu začít odmítat profily.
     */
    public function test_an_unreadable_rule_is_skipped_and_reported(): void
    {
        $this->fake('Ostrava');

        $rules = "tohle není pravidlo\ncity != Brno";

        $source = $this->source($rules);
        $rulesService = app(ContentRules::class);

        $this->assertCount(1, $rulesService->rules($source));
        $this->assertCount(1, $rulesService->problems($source));
        $this->assertStringContainsString('Řádek 1', $rulesService->problems($source)[0]);
    }

    /** Neplatný regulární výraz nesmí shodit běh na položce, se kterou nesouvisí. */
    public function test_a_broken_regular_expression_is_refused_at_parse_time(): void
    {
        $this->fake('Brno');

        $source = $this->source('about_me ~ /(nezavřená');

        $this->assertSame([], app(ContentRules::class)->rules($source));

        app(ScrapeRunner::class)->run($source, ['pages' => 1]);

        $this->assertSame(ScrapeItem::STATUS_PENDING, ScrapeItem::first()->status);
    }

    /** Pravidlo o seznamu má fungovat, i když má osm položek. */
    public function test_a_rule_can_look_inside_a_list(): void
    {
        $rules = app(ContentRules::class);

        $source = $this->source('services ~ /eskort/i');

        $this->assertNotNull($rules->violation($source, ['services' => ['masáž', 'eskort na večeři']]));
        $this->assertNull($rules->violation($source, ['services' => ['masáž', 'společnice']]));
    }

    /** Bez pravidel se nic neděje. */
    public function test_no_rules_means_no_effect(): void
    {
        $this->fake('Ostrava', 'whatsapp +420111222333');

        app(ScrapeRunner::class)->run($this->source(null), ['pages' => 1]);

        $this->assertSame(ScrapeItem::STATUS_PENDING, ScrapeItem::first()->status);
    }
}
