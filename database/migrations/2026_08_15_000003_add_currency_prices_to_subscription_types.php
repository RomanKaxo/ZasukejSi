<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Prices per currency.
 *
 * `price` was a single decimal with no currency attached, so the same number
 * stood for korunas on the member plans and for euros on the provider ones.
 * Each currency now has its own column and the site shows the one matching the
 * visitor's locale — no conversion at display time, because a rate that drifts
 * would quietly misquote the price.
 *
 * `price` stays as the Czech amount so nothing reading it breaks.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_types', function (Blueprint $table) {
            $table->decimal('price_czk', 10, 2)->nullable()->after('price');
            $table->decimal('price_eur', 10, 2)->nullable()->after('price_czk');
            $table->decimal('price_usd', 10, 2)->nullable()->after('price_eur');
        });

        // Member plans were seeded in korunas, provider plans in euros.
        DB::table('subscription_types')->where('audience', 'member')->update([
            'price_czk' => DB::raw('price'),
        ]);

        DB::table('subscription_types')->where('audience', '!=', 'member')->update([
            'price_eur' => DB::raw('price'),
        ]);
    }

    public function down(): void
    {
        Schema::table('subscription_types', function (Blueprint $table) {
            $table->dropColumn(['price_czk', 'price_eur', 'price_usd']);
        });
    }
};
