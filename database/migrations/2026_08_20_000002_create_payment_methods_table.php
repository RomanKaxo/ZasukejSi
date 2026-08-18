<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How a subscription can be paid for.
 *
 * There was one way and it was Stripe, configured in the environment file —
 * so switching a payment on or off, or correcting a key, meant a deploy and
 * somebody with shell access. That is fine for a developer and impossible for
 * the person actually running the site, who is the one who knows the bank
 * account changed.
 *
 * The row holds only what differs between installations: whether the method is
 * offered, in what order, and its credentials. What each method *is* — what
 * fields it needs, how it charges — stays in code, so a method cannot be
 * invented by typing a new code into the database.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();

            // Odpovídá klíči v registru metod v kódu.
            $table->string('code', 40)->unique();

            $table->boolean('is_enabled')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);

            // Tokeny, čísla účtů — co která metoda potřebuje.
            $table->json('settings')->nullable();

            // Co se kupujícímu ukáže navíc, přeložitelné.
            $table->json('instructions')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
