<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The currency a profile quotes its price list in.
 *
 * The admin form hardcoded a "$" prefix on every price field, so a Czech
 * provider entering korunas had them labelled as dollars. The amount now
 * carries the currency it was entered in.
 *
 * Existing rows are assumed to be korunas for Czech and Slovak profiles and
 * euros elsewhere, which matches how the price list was being filled in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('price_currency', 3)->default('CZK')->after('global_prices');
        });

        DB::table('profiles')
            ->whereNotIn(DB::raw('UPPER(COALESCE(country_code, ""))'), ['CZ', 'SK'])
            ->update(['price_currency' => 'EUR']);
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('price_currency');
        });
    }
};
