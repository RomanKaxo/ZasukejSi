<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('display_name');
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('country_code')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->text('about')->nullable();
            $table->json('availability_hours')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->boolean('is_public')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'is_public', 'verified_at']);
            $table->index('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
