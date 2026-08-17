<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The FAQ page gets its Czech name.
 *
 * The design calls it „Časté dotazy" — in the footer and in the header menu.
 * The footer link already said that (it comes from a translation key), but the
 * page's own title did not, so the heading and the link disagreed.
 *
 * The slug stays `faq`: it is in links and in search results, and renaming it
 * would break them for no gain.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->retitle('FAQ', 'Časté dotazy');
        $this->relabelFooterItem('FAQ', 'Časté dotazy');
    }

    public function down(): void
    {
        $this->retitle('Časté dotazy', 'FAQ');
        $this->relabelFooterItem('Časté dotazy', 'FAQ');
    }

    /**
     * The footer link carries its own label, so renaming the page alone left
     * the footer still saying „FAQ" — which the design spells out in full.
     *
     * The header menu keeps „FAQ": that is what the design shows there, and it
     * comes from a translation key rather than from either of these rows.
     */
    private function relabelFooterItem(string $from, string $to): void
    {
        $page = DB::table('pages')->where('slug', 'faq')->first();

        if (! $page) {
            return;
        }

        $item = DB::table('footer_menu_items')->where('page_id', $page->id)->first();

        if (! $item) {
            return;
        }

        $labels = json_decode($item->label ?? '{}', true);

        if (! is_array($labels) || ($labels['cs'] ?? null) !== $from) {
            return;
        }

        $labels['cs'] = $to;

        DB::table('footer_menu_items')
            ->where('id', $item->id)
            ->update(['label' => json_encode($labels, JSON_UNESCAPED_UNICODE)]);
    }

    /** Rewrites only the Czech title, and only when it still says what we expect. */
    private function retitle(string $from, string $to): void
    {
        $page = DB::table('pages')->where('slug', 'faq')->first();

        if (! $page) {
            return;
        }

        $titles = json_decode($page->title ?? '{}', true);

        if (! is_array($titles) || ($titles['cs'] ?? null) !== $from) {
            // Somebody has renamed it by hand — leave their wording alone.
            return;
        }

        $titles['cs'] = $to;

        DB::table('pages')
            ->where('id', $page->id)
            ->update(['title' => json_encode($titles, JSON_UNESCAPED_UNICODE)]);
    }
};
