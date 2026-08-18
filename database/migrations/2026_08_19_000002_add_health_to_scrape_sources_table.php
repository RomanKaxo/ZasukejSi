<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Whether a source still works, and what to do when it stops.
 *
 * A scheduled source that starts refusing us — a 403 after an IP block, a
 * redesign that broke every selector — kept its slot in the queue and went on
 * asking every few hours, forever. Nobody was told, and the site got hammered
 * by a bot that could not read it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scrape_sources', function (Blueprint $table) {
            // Reset by the first run that succeeds.
            $table->unsignedSmallInteger('consecutive_failures')->default(0)->after('next_run_at');

            // Scheduling stops itself here. Manual runs still work, so the
            // operator can try a fix without re-enabling anything.
            $table->timestamp('paused_at')->nullable()->after('consecutive_failures');
            $table->string('paused_reason', 500)->nullable()->after('paused_at');

            $table->timestamp('last_success_at')->nullable()->after('paused_reason');
        });
    }

    public function down(): void
    {
        Schema::table('scrape_sources', function (Blueprint $table) {
            $table->dropColumn([
                'consecutive_failures',
                'paused_at',
                'paused_reason',
                'last_success_at',
            ]);
        });
    }
};
