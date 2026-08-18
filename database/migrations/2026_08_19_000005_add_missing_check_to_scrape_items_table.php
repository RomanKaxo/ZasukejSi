<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Profiles that have vanished from the site they were taken from.
 *
 * A woman who stops advertising is removed from the source, and until now
 * nothing noticed: her profile stayed on our site, public and looking current,
 * indefinitely. That is the one failure mode of a scraped catalogue that costs
 * the visitor something real — a listing that leads nowhere.
 *
 * Noticing is automatic. Doing anything about it is not: the columns record
 * what was seen and what the operator then decided. Nothing here ever deletes
 * or hides a profile on its own — that call belongs to a person, because the
 * source going quiet has several innocent explanations and only one bad one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scrape_items', function (Blueprint $table) {
            // Kdy byla stránka poprvé pryč. Nuluje se, jakmile se zase objeví.
            $table->timestamp('missing_since')->nullable()->after('retry_after');

            // Kolikrát za sebou to bylo potvrzeno. Jedna 404 je klidně výpadek.
            $table->unsignedSmallInteger('missing_checks')->default(0)->after('missing_since');

            $table->timestamp('missing_checked_at')->nullable()->after('missing_checks');

            // null = čeká na rozhodnutí, 'kept' = necháváme, 'removed' = vyřešeno
            $table->string('missing_resolution', 20)->nullable()->after('missing_checked_at');
            $table->timestamp('missing_resolved_at')->nullable()->after('missing_resolution');
            $table->foreignId('missing_resolved_by')->nullable()->after('missing_resolved_at')
                ->constrained('users')->nullOnDelete();
        });

        Schema::table('scrape_items', function (Blueprint $table) {
            $table->index(['missing_since', 'missing_resolution'], 'scrape_items_missing_index');
        });
    }

    public function down(): void
    {
        Schema::table('scrape_items', function (Blueprint $table) {
            $table->dropIndex('scrape_items_missing_index');
            $table->dropConstrainedForeignId('missing_resolved_by');
            $table->dropColumn([
                'missing_since',
                'missing_checks',
                'missing_checked_at',
                'missing_resolution',
                'missing_resolved_at',
            ]);
        });
    }
};
