<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Values a source offered that our own catalogue does not know.
 *
 * The importer never invents catalogue entries — a scraped list must not be
 * able to extend our taxonomy on its own. That is the right rule, but it was
 * silent: a harvest of Brno brought 58 distinct service names, our catalogue
 * knew 10, and the other 48 were dropped without anybody being told.
 *
 * They land here instead, deduplicated and counted, so an admin can decide
 * which ones become real entries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scrape_unknown_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('scrape_source_id')->nullable()->constrained()->nullOnDelete();

            // Which of our fields it was destined for, e.g. "services".
            $table->string('field', 64);

            // As the source wrote it — this is what an admin reads and what
            // becomes the catalogue entry's name.
            $table->string('value');

            // Diacritics, case and spacing dropped, so "GFE - Společnice" and
            // "gfe společnice" are one row rather than two.
            $table->string('normalized', 191);

            $table->unsignedInteger('occurrences')->default(1);

            $table->string('status', 16)->default('pending');

            // What it became once approved, so the link is not guesswork.
            $table->string('created_type')->nullable();
            $table->unsignedBigInteger('created_id')->nullable();

            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            // One row per value per field, whatever spelling it arrived in.
            $table->unique(['field', 'normalized']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrape_unknown_values');
    }
};
