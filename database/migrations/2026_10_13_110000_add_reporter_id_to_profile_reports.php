<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_reports', function (Blueprint $table) {
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('profile_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reporter_id');
        });
    }
};
