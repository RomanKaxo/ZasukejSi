<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every run already narrates itself — which listing pages it read, how many
 * links each held, what robots.txt asked for, and for a dry run the values it
 * extracted. That narration only ever reached the console command; from the
 * admin it was computed and thrown away, so a test run could report "3 found"
 * without being able to show what was in them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scrape_runs', function (Blueprint $table) {
            $table->longText('log')->nullable()->after('error');
        });
    }

    public function down(): void
    {
        Schema::table('scrape_runs', function (Blueprint $table) {
            $table->dropColumn('log');
        });
    }
};
