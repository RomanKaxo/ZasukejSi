<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Whether a plan appears on the public VIP & Premium page.
 *
 * `is_active` already existed, but it means something else: an inactive plan
 * cannot be bought at all, including by somebody renewing from inside their
 * account. There was no way to say „this one is still sold, just not
 * advertised on the landing page" — a seasonal tier, a plan being phased out,
 * one negotiated with individual providers.
 *
 * Two separate questions, so two separate columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_types', function (Blueprint $table) {
            $table->boolean('show_on_plans_page')->default(true)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_types', function (Blueprint $table) {
            $table->dropColumn('show_on_plans_page');
        });
    }
};
