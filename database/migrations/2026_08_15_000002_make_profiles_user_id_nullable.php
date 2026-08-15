<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A scraped profile has no owner yet.
 *
 * Imports land as unpublished drafts that nobody has claimed, so `user_id` has
 * to be allowed to be null until a provider account is attached. Existing rows
 * are unaffected; the foreign key already cascades on delete.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Rows without an owner would break the NOT NULL constraint, so they
        // are removed before it comes back.
        \DB::table('profiles')->whereNull('user_id')->delete();

        Schema::table('profiles', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
