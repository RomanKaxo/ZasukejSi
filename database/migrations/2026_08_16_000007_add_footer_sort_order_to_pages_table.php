<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The footer and the header menu shared one `sort_order`, so the footer could
 * not be arranged without dragging the top navigation along with it.
 *
 * Null means "follow the menu order", which is what every existing page did
 * until now — so nothing moves until an admin says so.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->integer('footer_sort_order')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('footer_sort_order');
        });
    }
};
