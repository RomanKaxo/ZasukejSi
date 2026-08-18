<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Detail pages that failed, so they can be tried again.
 *
 * A detail page that timed out left no trace at all: the counter went up, the
 * log said one line, and the address was gone. The profile came back only when
 * somebody walked the whole listing again — and with change-aware runs, which
 * only ask for what the site says has moved, quite possibly never.
 *
 * A failure is now a row like any other, with the count of attempts and when
 * the next one is due. Retrying with a growing gap rather than immediately,
 * because whatever broke the first time is rarely fixed a second later.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scrape_items', function (Blueprint $table) {
            $table->unsignedSmallInteger('attempts')->default(0)->after('status');
            $table->timestamp('last_attempt_at')->nullable()->after('attempts');

            // Null means „not waiting for anything": either it works, or it
            // has failed so often that automatic retries have given up.
            $table->timestamp('retry_after')->nullable()->after('last_attempt_at');
        });

        Schema::table('scrape_items', function (Blueprint $table) {
            $table->index(['scrape_source_id', 'status', 'retry_after'], 'scrape_items_retry_index');
        });
    }

    public function down(): void
    {
        Schema::table('scrape_items', function (Blueprint $table) {
            $table->dropIndex('scrape_items_retry_index');
            $table->dropColumn(['attempts', 'last_attempt_at', 'retry_after']);
        });
    }
};
