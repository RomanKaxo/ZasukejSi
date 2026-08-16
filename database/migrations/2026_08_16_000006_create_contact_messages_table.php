<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Messages sent from the contact page.
 *
 * The sender's own words are kept verbatim; everything else — who was signed
 * in, which profile they were acting as, what language the site was in — is
 * recorded alongside so an admin answering the message has the context.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('email');
            $table->text('message');

            // Set when the sender was signed in. Nulled rather than cascaded on
            // delete: the message itself is still a record that must survive
            // the account being removed.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('profile_id')->nullable()->constrained()->nullOnDelete();

            $table->string('status')->default('new');
            $table->timestamp('read_at')->nullable();
            $table->text('admin_note')->nullable();

            $table->string('locale', 8)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
