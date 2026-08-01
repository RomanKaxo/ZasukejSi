<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-user read/archived state for GLOBAL notifications.
     *
     * A global notification is a single row shared by every user, so its
     * `read_at` / `archived_at` columns cannot hold per-user state — previously
     * any user archiving or deleting a global notification mutated (or removed)
     * it for the entire platform. This pivot stores that state per user instead.
     */
    public function up(): void
    {
        Schema::create('notification_user_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique(['notification_id', 'user_id']);
            $table->index(['user_id', 'archived_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_user_states');
    }
};
