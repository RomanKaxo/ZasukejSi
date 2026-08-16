<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The footer's menu, managed the way the header's is.
 *
 * Until now the footer could only list CMS pages under their own titles, so it
 * could not carry a link whose wording differs from the page title, two links
 * pointing at one page, or anything outside the CMS. A menu item is its own
 * thing: a label, a target, a column and a position.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_menu_items', function (Blueprint $table) {
            $table->id();

            // Translatable, so the wording follows the site language.
            $table->json('label');

            // Exactly one of these carries the target. A page keeps the link
            // correct when its slug changes; a url covers everything else.
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->string('url')->nullable();

            $table->unsignedTinyInteger('column')->default(1);
            $table->integer('sort_order')->default(0);
            $table->boolean('opens_in_new_tab')->default(false);
            $table->boolean('is_visible')->default(true);

            $table->timestamps();

            $table->index(['is_visible', 'column', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_menu_items');
    }
};
