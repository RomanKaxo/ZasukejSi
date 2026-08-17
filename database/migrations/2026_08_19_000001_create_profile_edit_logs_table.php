<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who changed what on a profile.
 *
 * An admin can edit somebody else's advertisement, including its price list
 * and its status. Until now that left no trace at all, so a provider asking
 * „kdo mi to přepsal" had no answer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_edit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();

            // Null when the change came from a command or a scraper import
            // rather than from a person.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // field => ['from' => …, 'to' => …]
            $table->json('change_set');

            $table->timestamp('created_at')->nullable()->index();

            $table->index(['profile_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_edit_logs');
    }
};
