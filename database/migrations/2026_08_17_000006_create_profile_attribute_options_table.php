<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Option lists for the profile's enumerable attributes.
 *
 * Eye colour, hair colour and length, bust type and shape, pubic hair and how
 * far somebody travels were all being scraped and then thrown away: the
 * importer had nowhere to put them, because the only list that existed was a
 * hardcoded `['A','B',…]` for bust size inside a Livewire component.
 *
 * With a table behind them the scraper can offer new values for approval the
 * same way it does for services, and the admin can manage the lists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_attribute_options', function (Blueprint $table) {
            $table->id();

            // Which list this belongs to, e.g. "eye_colour".
            $table->string('attribute', 32);

            // Translatable, so a list can read properly in every language.
            $table->json('label');

            // Diacritics, case and spacing dropped, so "Oříšková" and
            // "oriskova" are one option rather than two.
            $table->string('normalized', 191);

            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['attribute', 'normalized']);
            $table->index(['attribute', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_attribute_options');
    }
};
