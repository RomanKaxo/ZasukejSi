<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A thin, admin-managed presentation layer over the data we already have.
 *
 * It deliberately stores no city, region or count data:
 *   - country names come from lang/{cs,en}/codes.php (306 entries, both locales)
 *   - regions come from cities.admin_name, via the existing
 *     (country_code, admin_name) index
 *   - profile counts are computed from `profiles` at read time
 *
 * All this table decides is *which* countries appear in the country lists, in
 * what order, and under what (optional) custom name — replacing three separate
 * hardcoded arrays, one of which repeated the same eight countries three times
 * with different numbers each time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();

            // ISO-3166-1 alpha-2, uppercase, matching profiles/cities.country_code.
            // Unique so the same country can never be listed twice.
            $table->char('code', 2)->unique();

            // Optional per-locale override; null means "use lang/*/codes.php".
            $table->json('name_override')->nullable();

            $table->integer('sort_order')->default(0);

            // A country stays in the list at zero profiles — the sidebar is a
            // stable navigation element, not a reflection of current stock.
            $table->boolean('is_visible')->default(true);

            $table->timestamps();

            $table->index(['is_visible', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
