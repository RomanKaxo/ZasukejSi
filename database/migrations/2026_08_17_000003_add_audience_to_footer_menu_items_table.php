<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who a footer link is for.
 *
 * The design carries "VIP účet pro dívky" and "Prémium účet pro pány" side by
 * side, which only makes sense to somebody who is not one of the two. A signed
 * in visitor should be offered the plan that applies to her or him; a visitor
 * who is not signed in has no role yet and gets the page that describes both.
 *
 * `all` is the default, so every existing item keeps showing to everybody.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('footer_menu_items', function (Blueprint $table) {
            $table->string('audience', 16)->default('all')->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('footer_menu_items', function (Blueprint $table) {
            $table->dropColumn('audience');
        });
    }
};
