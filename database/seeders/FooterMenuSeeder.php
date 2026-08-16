<?php

namespace Database\Seeders;

use App\Models\FooterMenuItem;
use App\Models\Page;
use App\Support\Locales;
use Illuminate\Database\Seeder;

/**
 * Turns the footer's current page list into editable menu items.
 *
 * The footer falls back to listing pages when no menu item exists, so this is
 * not required for the site to work — it just means the admin starts from what
 * the footer already shows instead of a blank screen.
 */
class FooterMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Never overwrite an arrangement somebody has already made.
        if (FooterMenuItem::query()->exists()) {
            return;
        }

        $pages = Page::query()
            ->where('display_in_footer', true)
            ->where('is_published', true)
            ->footerOrdered()
            ->get();

        if ($pages->isEmpty()) {
            return;
        }

        // Same three columns, filled the same way the template used to fill
        // them, so the footer looks identical the moment this runs.
        $perColumn = (int) ceil($pages->count() / 3);

        foreach ($pages->chunk($perColumn)->values() as $columnIndex => $column) {
            foreach ($column->values() as $position => $page) {
                FooterMenuItem::create([
                    'label' => self::labelPerLocale($page),
                    'page_id' => $page->id,
                    'column' => min(3, $columnIndex + 1),
                    'sort_order' => ($position + 1) * 10,
                    'is_visible' => true,
                ]);
            }
        }
    }

    /** @return array<string, string> */
    private static function labelPerLocale(Page $page): array
    {
        $labels = [];

        foreach (Locales::codes() as $locale) {
            $labels[$locale] = $page->getTranslation('title', $locale, false)
                ?: $page->getTranslation('title', Locales::source());
        }

        return array_filter($labels, fn ($label) => filled($label));
    }
}
