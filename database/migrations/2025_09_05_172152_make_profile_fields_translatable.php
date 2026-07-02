<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, create temporary columns
        Schema::table('profiles', function (Blueprint $table) {
            $table->json('display_name_temp')->nullable();
            $table->json('about_temp')->nullable();
        });

        // Convert existing data to JSON format
        DB::table('profiles')->get()->each(function ($profile) {
            $displayNameJson = [
                'en' => $profile->display_name,
                'cs' => $profile->display_name // Default to same value
            ];
            
            $aboutJson = [
                'en' => $profile->about,
                'cs' => $profile->about // Default to same value  
            ];

            DB::table('profiles')
                ->where('id', $profile->id)
                ->update([
                    'display_name_temp' => json_encode($displayNameJson),
                    'about_temp' => json_encode($aboutJson),
                ]);
        });

        // Drop old columns and rename temporary ones
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'about']);
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->renameColumn('display_name_temp', 'display_name');
            $table->renameColumn('about_temp', 'about');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Create temporary string columns
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('display_name_temp')->nullable();
            $table->text('about_temp')->nullable();
        });

        // Convert JSON data back to string format
        DB::table('profiles')->get()->each(function ($profile) {
            $displayName = json_decode($profile->display_name, true);
            $about = json_decode($profile->about, true);

            DB::table('profiles')
                ->where('id', $profile->id)
                ->update([
                    'display_name_temp' => $displayName['en'] ?? '',
                    'about_temp' => $about['en'] ?? '',
                ]);
        });

        // Drop JSON columns and rename temporary ones
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'about']);
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->renameColumn('display_name_temp', 'display_name');
            $table->renameColumn('about_temp', 'about');
        });
    }
};
