<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Translations imported into the `translations` table override the lang files,
 * so editing a file leaves the admin looking at the old wording.
 *
 * "Určuje pořadí v menu i v patičce" stopped being true the moment the footer
 * got its own order. Only rows that still hold the shipped default are
 * touched — anything an admin has reworded is left alone.
 */
return new class extends Migration
{
    /** @var array<string, array<string, array{0: string, 1: string}>> */
    private array $replacements = [
        'cs' => [
            'form.sort_order' => ['Pořadí', 'Pořadí v menu'],
            'form.sort_order_helper' => [
                'Určuje pořadí v menu i v patičce. Nižší číslo se zobrazí dřív.',
                'Určuje pořadí v horním menu. Nižší číslo se zobrazí dřív.',
            ],
        ],
        'en' => [
            'form.sort_order' => ['Order', 'Menu order'],
            'form.sort_order_helper' => [
                'Controls the order in the menu and the footer. Lower numbers appear first.',
                'Controls the order in the top menu. Lower numbers appear first.',
            ],
        ],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('translations')) {
            return;
        }

        foreach ($this->replacements as $locale => $keys) {
            foreach ($keys as $key => [$old, $new]) {
                DB::table('translations')
                    ->where('locale', $locale)
                    ->where('group', 'pages')
                    ->where('key', $key)
                    ->where('value', $old)
                    ->update(['value' => $new, 'default_value' => $new]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('translations')) {
            return;
        }

        foreach ($this->replacements as $locale => $keys) {
            foreach ($keys as $key => [$old, $new]) {
                DB::table('translations')
                    ->where('locale', $locale)
                    ->where('group', 'pages')
                    ->where('key', $key)
                    ->where('value', $new)
                    ->update(['value' => $old, 'default_value' => $old]);
            }
        }
    }
};
