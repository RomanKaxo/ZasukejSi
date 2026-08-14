<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Explicit ordering for CMS pages.
 *
 * The header navigation and the footer both ordered by `created_at`, so the
 * menu order was whatever order the seeder happened to insert in — FAQ before
 * VIP & Premium, where the design has it the other way round. There was no way
 * to fix it from the admin at all.
 *
 * Existing rows are numbered by their current `created_at` so nothing visibly
 * moves on deploy; the design's order is then applied by PageSeeder.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('display_in_footer');
            $table->index(['display_in_menu', 'sort_order']);
        });

        $order = 0;

        foreach (DB::table('pages')->orderBy('created_at')->pluck('id') as $id) {
            DB::table('pages')->where('id', $id)->update(['sort_order' => ($order += 10)]);
        }
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex(['display_in_menu', 'sort_order']);
            $table->dropColumn('sort_order');
        });
    }
};
