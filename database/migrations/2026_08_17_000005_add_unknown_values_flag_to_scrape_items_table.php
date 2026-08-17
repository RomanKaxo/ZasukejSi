<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marks an item that mentions something our catalogue does not know.
 *
 * Needed to tell two situations apart once the value is finally added: an item
 * that was held back for it should be released automatically, while an item
 * nobody ever blocked stays for the reviewer to approve by hand.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scrape_items', function (Blueprint $table) {
            $table->timestamp('unknown_values_at')->nullable()->after('duplicate_checked_at');
            $table->index('unknown_values_at');
        });
    }

    public function down(): void
    {
        Schema::table('scrape_items', function (Blueprint $table) {
            $table->dropIndex(['unknown_values_at']);
            $table->dropColumn('unknown_values_at');
        });
    }
};
