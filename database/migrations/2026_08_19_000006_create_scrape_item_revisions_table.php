<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What changed at the source between one run and the next.
 *
 * A re-scrape overwrote the item in place. The counter said „aktualizováno" and
 * that was the whole story: which field moved, from what to what, whether a
 * photograph appeared or vanished — all of it gone the moment it was written.
 *
 * That is the difference between a catalogue and a snapshot. A price that
 * doubled overnight, a phone number that changed, a profile that came back
 * after being pulled — these are the things an operator needs to see, and none
 * of them are visible in a row that only ever holds the latest values.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scrape_item_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scrape_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scrape_run_id')->nullable()->constrained()->nullOnDelete();

            // pole => ['from' => …, 'to' => …]
            $table->json('changes')->nullable();

            $table->json('images_added')->nullable();
            $table->json('images_removed')->nullable();

            // Změna v poli, které stojí za pozornost (cena, telefon, město).
            // Sloupec, ne dopočet: co bylo důležité tehdy, se dá změnit až
            // potom, a historie se tím nesmí přepsat.
            $table->boolean('is_notable')->default(false);

            $table->timestamps();

            $table->index(['scrape_item_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrape_item_revisions');
    }
};
