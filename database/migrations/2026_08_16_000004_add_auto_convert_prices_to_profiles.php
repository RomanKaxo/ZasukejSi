<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Whether a provider wants the currencies she did not fill in to be worked out
 * from the admin's exchange rate.
 *
 * Off by default and deliberately opt-in: a rate moves, so a converted price
 * can differ from what she meant to charge. Nothing she typed is ever
 * recalculated — this only fills the gaps.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->boolean('auto_convert_prices')->default(false)->after('price_currency');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('auto_convert_prices');
        });
    }
};
