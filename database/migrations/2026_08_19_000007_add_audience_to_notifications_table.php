<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who a notification is for.
 *
 * There was one channel and it was the visitor's bell. The scraper's „profily
 * zmizely ze zdroje" therefore went out as a global notification — which is to
 * say, to every woman and every member on the site, about a maintenance
 * question that is none of their business and that they cannot act on.
 *
 * The column separates the two audiences at the source rather than by
 * convention, so a notification meant for the operator cannot reach the front
 * end by somebody forgetting a scope.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // 'public' = návštěvníkům, 'admin' = jen do administrace
            $table->string('audience', 20)->default('public')->after('type');
            $table->index(['audience', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['audience', 'created_at']);
            $table->dropColumn('audience');
        });
    }
};
