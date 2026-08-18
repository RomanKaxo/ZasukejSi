<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How a subscription was paid for, and whether the money has arrived.
 *
 * With a card the two questions are the same question: the gateway answers
 * before the buyer leaves the page. A bank transfer separates them by a day or
 * three, and the gap is the whole difference — an order exists, the customer
 * has done their part, and nobody can say yet whether the amount landed.
 *
 * `status = pending` already existed for exactly this shape. What was missing
 * was the paperwork around it: which method, what reference the customer put
 * on the payment, when it was confirmed and by whom.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['subscriptions', 'member_subscriptions'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('payment_method', 40)->nullable()->after('status');

                // Variabilní symbol. Bez něj je platba na účtu anonymní částka.
                $blueprint->string('payment_reference', 40)->nullable()->after('payment_method');

                $blueprint->timestamp('paid_at')->nullable()->after('payment_reference');
                $blueprint->foreignId('payment_confirmed_by')->nullable()->after('paid_at')
                    ->constrained('users')->nullOnDelete();
            });

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->index(['status', 'payment_method'], $table . '_payment_index');
            });
        }
    }

    public function down(): void
    {
        foreach (['subscriptions', 'member_subscriptions'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropIndex($table . '_payment_index');
                $blueprint->dropConstrainedForeignId('payment_confirmed_by');
                $blueprint->dropColumn(['payment_method', 'payment_reference', 'paid_at']);
            });
        }
    }
};
