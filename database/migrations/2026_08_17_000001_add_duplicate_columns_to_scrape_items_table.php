<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Until now the scraper only recognised a repeat of itself: the same
 * `external_id` from the same source. The same woman advertised on two
 * different sites, or already present as a profile on our own, was invisible —
 * and importing her created a second profile.
 *
 * The result is stored rather than computed on the fly, so the review queue
 * can show and filter it without a query per row. It is a snapshot; the
 * queue carries an action to take it again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scrape_items', function (Blueprint $table) {
            // The strongest candidate found. Nulled rather than cascaded on
            // delete: losing the profile does not make the item a new one.
            $table->foreignId('duplicate_profile_id')->nullable()->after('imported_profile_id')
                ->constrained('profiles')->nullOnDelete();

            $table->foreignId('duplicate_item_id')->nullable()->after('duplicate_profile_id')
                ->constrained('scrape_items')->nullOnDelete();

            // Why the two were considered the same, in the reviewer's language.
            $table->string('duplicate_reason')->nullable()->after('duplicate_item_id');
            $table->timestamp('duplicate_checked_at')->nullable()->after('duplicate_reason');

            $table->index('duplicate_profile_id');
        });
    }

    public function down(): void
    {
        Schema::table('scrape_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('duplicate_profile_id');
            $table->dropConstrainedForeignId('duplicate_item_id');
            $table->dropColumn(['duplicate_reason', 'duplicate_checked_at']);
        });
    }
};
