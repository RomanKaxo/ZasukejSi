<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A subscription that has been ordered but not paid for has not started.
 *
 * `starts_at` and `ends_at` were required, which was true while the only way
 * to buy was a card: the gateway answered before the buyer left the page, so
 * an order and a running subscription were the same thing.
 *
 * A bank transfer takes days. Filling the dates in at order time to satisfy
 * the column would be a lie in the data — a subscription that "runs" from a
 * date on which nobody had paid, and which every date-based report would count.
 * Null is what „not started" actually is.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['subscriptions', 'member_subscriptions'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->timestamp('starts_at')->nullable()->change();
                $blueprint->timestamp('ends_at')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Zpět se to obrátit nedá bez ztráty: nezaplacené objednávky žádné
        // datum nemají a vymyslet jim ho by znamenalo tvrdit, že běží.
    }
};
