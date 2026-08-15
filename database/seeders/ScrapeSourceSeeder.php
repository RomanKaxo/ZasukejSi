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
                    // The listing lives at /eskorty/<země>/ (plural). /eskort/
                    // is the prefix of a *detail* page, so pointing the listing
                    // at it returned a page with no profile links at all.
                    'listing_path' => '/eskorty/ceska-republika',
                    'pagination_pattern' => '/{page}/',
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

        // Attributes sit in `div.params > div`, each shaped as
        // "<span>Popisek:</span>Hodnota". Selecting by the span's label is the
        // only stable handle — the divs carry no per-attribute class.
        $param = fn (string $label) => '//div[contains(@class,"params")]/div[starts-with(normalize-space(span), "' . $label . '")]';

        // Everything after the colon, which is the value.
        $afterColon = [['regex', '/:\s*(.*)$/u'], 'trim'];

        $maps = [
            // The h1 reads "Eskort <jméno> - <město> / <země>".
            ['display_name', 'h1', 'text', false, ['collapse_whitespace', ['regex', '/^(?:Eskort\s+)?(.+?)\s+-\s+/u'], 'trim'], true, 10],
            ['city', $param('Lokace'), 'text', false, ['collapse_whitespace', ['regex', '/:\s*([^\/]+?)\s*\//u'], 'trim'], false, 20],
            ['country_code', $param('Lokace'), 'text', false, ['collapse_whitespace', ['regex', '/\/\s*(.+)$/u'], 'trim', ['map', ['Česká republika' => 'CZ', 'Slovensko' => 'SK', 'Rakousko' => 'AT', 'Německo' => 'DE', 'Polsko' => 'PL']]], false, 25],
            ['about', '.description, .about, [class*="description"]', 'text', false, ['collapse_whitespace'], false, 30],

            ['age', $param('Věk'), 'text', false, ['collapse_whitespace', 'int'], false, 40],
            // "165 cm / 5'5''" — take the centimetres, not the feet.
            ['card_height_cm', $param('Výška'), 'text', false, ['collapse_whitespace', ['regex', '/(\d{2,3})\s*cm/u'], 'int'], false, 50],
            ['weight_kg', $param('Váha'), 'text', false, ['collapse_whitespace', ['regex', '/(\d{2,3})\s*kg/u'], 'int'], false, 60],
            ['bust_size', $param('Velikost poprsí'), 'text', false, array_merge(['collapse_whitespace'], $afterColon), false, 70],
            ['nationality', $param('Národnost'), 'text', false, array_merge(['collapse_whitespace'], $afterColon), false, 80],
            ['languages', $param('Jazyk'), 'text', false, array_merge(['collapse_whitespace'], $afterColon), false, 90],

            // Captured for review; the profile has no column for them yet.
            ['eye_colour', $param('Oči'), 'text', false, array_merge(['collapse_whitespace'], $afterColon), false, 100],
            ['hair_colour', $param('Barva vlasů'), 'text', false, array_merge(['collapse_whitespace'], $afterColon), false, 110],
            ['hair_length', $param('Délka vlasů'), 'text', false, array_merge(['collapse_whitespace'], $afterColon), false, 120],
            ['bust_type', $param('Typ poprsí'), 'text', false, array_merge(['collapse_whitespace'], $afterColon), false, 130],
            ['pubic_hair', $param('Pubické ochlupení'), 'text', false, array_merge(['collapse_whitespace'], $afterColon), false, 140],
            ['travels', $param('Cestuje'), 'text', false, array_merge(['collapse_whitespace'], $afterColon), false, 150],
            ['gender', $param('Pohlaví'), 'text', false, array_merge(['collapse_whitespace'], $afterColon), false, 160],

            // The page's only table is the service list, with the name in the
            // row's th. position()>1 skips the "Služby" header row.
            ['services', '//table//tr[position()>1]/th[1]', 'text', true, ['collapse_whitespace', 'compact', 'unique'], false, 170],

            ['photo_count', 'a.js-gallery', 'count', false, [], false, 200],
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
