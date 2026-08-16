<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A source could only be harvested by somebody clicking a button or typing a
 * console command. Anything recurring meant remembering to do it.
 *
 * Off by default — an existing source keeps behaving exactly as it does now
 * until an interval is set for it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scrape_sources', function (Blueprint $table) {
            $table->unsignedSmallInteger('schedule_hours')->nullable()->after('is_enabled');
            $table->timestamp('next_run_at')->nullable()->after('schedule_hours');
            $table->unsignedSmallInteger('schedule_pages')->nullable()->after('next_run_at');
            $table->unsignedSmallInteger('schedule_limit')->nullable()->after('schedule_pages');

            $table->index('next_run_at');
        });
    }

    public function down(): void
    {
        Schema::table('scrape_sources', function (Blueprint $table) {
            $table->dropIndex(['next_run_at']);
            $table->dropColumn(['schedule_hours', 'next_run_at', 'schedule_pages', 'schedule_limit']);
        });
    }
};
