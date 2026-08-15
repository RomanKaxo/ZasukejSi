<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Currencies the site can quote prices in.
 *
 * Previously a hardcoded list of three in App\Support\Currencies, which meant
 * adding one — or turning one off for a season — took a deploy.
 *
 * `exchange_rate` is how many units of this currency one unit of the base
 * currency buys. It is only used when a provider asks for the other amounts
 * to be filled in automatically; a price typed by hand is never overwritten,
 * because a rate that drifts would silently change what a customer is
 * charged.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('symbol', 8);
            $table->json('name');
            $table->decimal('exchange_rate', 14, 6)->default(1);
            // Exactly one row is the base; its rate is 1 by definition.
            $table->boolean('is_base')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
