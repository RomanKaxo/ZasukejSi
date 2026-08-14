<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Subscription plans now serve two different audiences.
 *
 * Until now every plan was a VIP tier bought by a female provider for her
 * profile. The design also calls for a paid "Premium" membership bought by a
 * male member — that is what unlocks the ratings the cards keep behind a lock,
 * and what the "Vaše Premium členství platí do …" banner reports.
 *
 * Rather than duplicating the whole plan/pricing structure, plans gain an
 * `audience` discriminator. Everything that existed before is a profile plan,
 * so the default backfills correctly and no existing query changes meaning —
 * callers that must not mix the two now use SubscriptionType::forProfiles()
 * / forMembers().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_types', function (Blueprint $table) {
            // Deliberately a string, not an ENUM: the profiles table already
            // showed how a MySQL-only ENUM makes later additions a migration
            // that has to be skipped on SQLite.
            $table->string('audience', 16)->default('profile')->after('slug');
            $table->index(['audience', 'is_active']);
        });

        DB::table('subscription_types')->update(['audience' => 'profile']);
    }

    public function down(): void
    {
        Schema::table('subscription_types', function (Blueprint $table) {
            $table->dropIndex(['audience', 'is_active']);
            $table->dropColumn('audience');
        });
    }
};
