<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A price per service, per currency.
 *
 * Services were a plain many-to-many with no price at all, so a provider could
 * list what she offers but not what it costs. `prices` holds one amount per
 * currency code, e.g. {"CZK": 2000, "EUR": 80}.
 *
 * Kept as JSON on the pivot rather than a row per currency: a service either
 * has a price list or it does not, and nothing queries or sorts by it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_service', function (Blueprint $table) {
            $table->json('prices')->nullable()->after('service_id');
            $table->text('note')->nullable()->after('prices');
        });
    }

    public function down(): void
    {
        Schema::table('profile_service', function (Blueprint $table) {
            $table->dropColumn(['prices', 'note']);
        });
    }
};
