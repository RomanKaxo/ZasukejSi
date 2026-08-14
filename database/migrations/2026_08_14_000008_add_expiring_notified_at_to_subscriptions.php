<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marks when the "your subscription expires soon" warning was sent.
 *
 * Without it the daily lifecycle command would re-send the same warning every
 * night for the whole week before expiry. The column is cleared on renewal, so
 * the next period warns again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->timestamp('expiring_notified_at')->nullable()->after('auto_renew');
        });

        Schema::table('member_subscriptions', function (Blueprint $table) {
            $table->timestamp('expiring_notified_at')->nullable()->after('auto_renew');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('expiring_notified_at');
        });

        Schema::table('member_subscriptions', function (Blueprint $table) {
            $table->dropColumn('expiring_notified_at');
        });
    }
};
