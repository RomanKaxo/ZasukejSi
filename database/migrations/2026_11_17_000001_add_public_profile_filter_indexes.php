<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Equality predicates precede the range/order columns. Include the
            // soft-delete predicate used by every public Eloquent listing.
            $table->index(['status', 'is_public', 'deleted_at', 'created_at', 'id'], 'profiles_public_created_idx');
            $table->index(['status', 'is_public', 'deleted_at', 'country_code', 'created_at', 'id'], 'profiles_public_country_created_idx');
            $table->index(['status', 'is_public', 'deleted_at', 'age'], 'profiles_public_age_idx');
            $table->index(['status', 'is_public', 'deleted_at', 'is_porn_actress', 'created_at', 'id'], 'profiles_public_actress_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex('profiles_public_created_idx');
            $table->dropIndex('profiles_public_country_created_idx');
            $table->dropIndex('profiles_public_age_idx');
            $table->dropIndex('profiles_public_actress_created_idx');
        });
    }
};
