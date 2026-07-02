<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Moving gender from profiles to users because:
     * - Gender now determines the user type (male vs female)
     * - Male users have different features and cannot create profiles
     * - Female users can create profiles as before
     */
    public function up(): void
    {
        // Add gender column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female'])->nullable()->after('phone');
        });

        // Migrate existing gender data from profiles to users
        DB::statement('
            UPDATE users 
            SET gender = (
                SELECT profiles.gender 
                FROM profiles 
                WHERE profiles.user_id = users.id
            )
            WHERE EXISTS (
                SELECT 1 FROM profiles WHERE profiles.user_id = users.id
            )
        ');

        // Remove gender column from profiles table
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add gender column back to profiles table
        Schema::table('profiles', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female'])->nullable()->after('user_id');
        });

        // Migrate gender data back from users to profiles
        DB::statement('
            UPDATE profiles 
            SET gender = (
                SELECT users.gender 
                FROM users 
                WHERE users.id = profiles.user_id
            )
        ');

        // Remove gender column from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('gender');
        });
    }
};
