<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill users left with a NULL gender.
     *
     * `2026_01_16_200000_move_gender_from_profiles_to_users` copied gender from
     * profiles to users, but any user whose profile had no gender (or who had no
     * profile at all) was left NULL. Those accounts can now reach neither the
     * provider area nor the member area once the `gender` middleware is active,
     * so they are resolved here:
     *
     *   - owns a Profile  -> 'female' (providers are the ones with a profile)
     *   - otherwise       -> 'male'   (plain members)
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNull('gender')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('profiles')
                    ->whereColumn('profiles.user_id', 'users.id');
            })
            ->update(['gender' => 'female']);

        DB::table('users')
            ->whereNull('gender')
            ->update(['gender' => 'male']);
    }

    /**
     * Not reversible: the original NULLs carried no information worth restoring.
     */
    public function down(): void
    {
        // no-op
    }
};
