<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ratings are collected as a percentage (the member UI offers 100 / 70 / 30)
 * but were stored only as 1-5 stars via a lossy 100=>5, 70=>4, 30=>2 mapping.
 *
 * Two consequences: star values 3 and 1 were unreachable, and the member
 * history bars converted back with rating/5*100, so a 70% rating was drawn
 * as 80% and a 30% one as 40%.
 *
 * The percentage becomes the stored truth; `rating` stays as a derived mirror
 * so existing star-based reads and the admin table keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_ratings', function (Blueprint $table) {
            $table->unsignedTinyInteger('percentage')->default(0)->after('rating');
        });

        // Reverse the original lossy mapping for rows written by the member UI.
        // Anything else falls back to the even star scale.
        DB::table('profile_ratings')->where('rating', 5)->update(['percentage' => 100]);
        DB::table('profile_ratings')->where('rating', 4)->update(['percentage' => 70]);
        DB::table('profile_ratings')->where('rating', 2)->update(['percentage' => 30]);
        DB::table('profile_ratings')->where('rating', 3)->update(['percentage' => 60]);
        DB::table('profile_ratings')->where('rating', 1)->update(['percentage' => 20]);
    }

    public function down(): void
    {
        Schema::table('profile_ratings', function (Blueprint $table) {
            $table->dropColumn('percentage');
        });
    }
};
