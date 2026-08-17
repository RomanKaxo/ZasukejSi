<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The reviews section works, so the menu stops calling it "coming soon".
 *
 * The label lives in the lang files, but the translations table overrides
 * them — so editing the lang file alone changed nothing on a seeded install.
 * These rows are the ones actually printed.
 */
return new class extends Migration
{
    /** @var array<string, array{0: string, 1: string}> locale => [old, new] */
    private const LABELS = [
        'cs' => ['Recenze - již brzy', 'Recenze'],
        'en' => ['Reviews - coming soon', 'Reviews'],
    ];

    public function up(): void
    {
        foreach (self::LABELS as $locale => [$old, $new]) {
            DB::table('translations')
                ->where('locale', $locale)
                ->where('group', 'front')
                ->where('key', 'account.sidebar.reviews')
                // Only the untouched default is replaced; wording somebody has
                // since edited by hand stays as they left it.
                ->where('value', $old)
                ->update(['value' => $new]);
        }
    }

    public function down(): void
    {
        foreach (self::LABELS as $locale => [$old, $new]) {
            DB::table('translations')
                ->where('locale', $locale)
                ->where('group', 'front')
                ->where('key', 'account.sidebar.reviews')
                ->where('value', $new)
                ->update(['value' => $old]);
        }
    }
};
