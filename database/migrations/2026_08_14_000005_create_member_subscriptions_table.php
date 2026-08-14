<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Paid membership for male members.
 *
 * The existing `subscriptions` table is keyed on `profile_id`, and a profile
 * only ever belongs to a female provider — so a member had no way to hold a
 * subscription at all, even though the design is built around one:
 *
 *   - profile cards hide the rating behind a lock icon
 *   - the profile detail page shows "Premium účet vám odemkne hodnocení"
 *   - five member pages show "Vaše Premium členství platí do …"
 *   - the member sidebar has a "Začít PRÉMIUM" call to action
 *
 * A separate table rather than a polymorphic rewrite of `subscriptions`:
 * `subscriptions.profile_id` is referenced in eleven places, including
 * Profile::isVip(), the VIP ordering in ProfileList and several Filament
 * resources. Widening that column would put all of them at risk for no gain.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_type_id')->constrained()->cascadeOnDelete();

            $table->timestamp('starts_at');
            $table->timestamp('ends_at');

            // Mirrors Subscription::STATUS_* so both kinds read the same way.
            $table->string('status', 16)->default('active');
            $table->timestamp('cancelled_at')->nullable();
            $table->boolean('auto_renew')->default(false);

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            // The membership lookup runs on nearly every authenticated page
            // (it decides whether ratings are visible), so it gets its own index.
            $table->index(['user_id', 'status', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_subscriptions');
    }
};
