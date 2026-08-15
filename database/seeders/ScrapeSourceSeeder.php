<?php

namespace Database\Seeders;

use App\Models\ScrapeFieldMap;
use App\Models\ScrapeSource;
use Illuminate\Database\Seeder;

/**
 * Starting configuration for the sources we scrape.
 *
 * The selectors are a starting point, not a contract — they are meant to be
 * corrected in the admin when the site changes, which is the whole reason the
 * mapping lives in the database.
 *
 * Sources are seeded disabled. Enabling one is a decision, not a side effect
 * of seeding.
 */
class ScrapeSourceSeeder extends Seeder
{
    public function run(): void
    {
        $source = ScrapeSource::updateOrCreate(
            ['slug' => 'eurogirlsescort-cz'],
            [
                'name' => 'EuroGirlsEscort.cz',
                'base_url' => 'https://www.eurogirlsescort.cz',
                'adapter' => 'generic',
                'is_enabled' => false,
                'notes' => 'Jejich robots.txt nezakazuje žádnou cestu, ale předepisuje Crawl-delay 5 s. '
                    . 'Ten je zároveň výchozí hodnotou zdroje; fetcher nikdy nejde pod hodnotu z robots.txt.',
                'settings' => [
                    'user_agent' => 'ZasukejSiBot/1.0 (+https://zasukejsi.cz/bot)',
                    'crawl_delay' => 5,
                    'timeout' => 30,
                    'max_pages' => 3,
                    'listing_path' => '/eskort',
                    'pagination_param' => 'page',
                    'detail_link_selector' => 'a[href*="/eskort/"]',
                    'detail_url_pattern' => '#/eskort/[^/]+/\d+/?$#',
                    'external_id_pattern' => '#/(\d+)/?$#',
                    'image_selector' => 'a.js-gallery',
                    'image_attribute' => 'href',
                    // The site serves 100x100 up to 1000x700 of the same photo;
                    // keep the largest and drop the thumbnails.
                    'image_prefer_pattern' => '#/1000x700/#',
                    'image_limit' => 10,
                    'respect_robots' => true,
                ],
            ]
        );

        $maps = [
            // The h1 reads "Eskort <jméno> - <město> / <země>"; the regex keeps
            // the name and the city map takes the segment after the dash.
            ['display_name', 'h1', 'text', false, ['collapse_whitespace', ['regex', '/^(?:Eskort\s+)?(.+?)\s+-\s+/u'], 'trim'], true, 10],
            ['city', 'h1', 'text', false, ['collapse_whitespace', ['regex', '/-\s*([^\/]+?)\s*\//u'], 'trim'], false, 20],
            ['about', '.description, .about, [class*="description"]', 'text', false, ['collapse_whitespace'], false, 30],
            // `compact` drops the rows whose regex matched nothing, so `first`
            // returns the row that actually carried the value.
            ['age', 'table tr', 'text', true, [['regex', '/(\d{2})\s*(?:let|years)/u'], 'compact', 'first', 'int'], false, 40],
            ['card_height_cm', 'table tr', 'text', true, [['regex', '/(\d{3})\s*cm/u'], 'compact', 'first', 'int'], false, 50],
            ['weight_kg', 'table tr', 'text', true, [['regex', '/(\d{2,3})\s*kg/u'], 'compact', 'first', 'int'], false, 60],
            ['photo_count', 'a.js-gallery', 'count', false, [], false, 70],
        ];

        foreach ($maps as [$field, $selector, $extract, $multiple, $transforms, $required, $order]) {
            ScrapeFieldMap::updateOrCreate(
                ['scrape_source_id' => $source->id, 'target_field' => $field],
                [
                    'selector' => $selector,
                    'extract' => $extract,
                    'multiple' => $multiple,
                    'transforms' => $transforms,
                    'is_required' => $required,
                    'sort_order' => $order,
                ]
            );
        }

        $this->command?->info('  ✓ Zdroj eurogirlsescort-cz připraven (vypnutý, ' . count($maps) . ' mapování)');
    }
}
