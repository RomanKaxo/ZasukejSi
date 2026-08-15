<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Scraper storage.
 *
 * Four tables so a source can be added and tuned from the admin without a
 * deploy: the site itself, the selector-to-field mapping, one row per run for
 * an audit trail, and the scraped rows waiting for review.
 *
 * Nothing here writes to `profiles`. Import is a separate, explicit step.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scrape_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('base_url');
            // Which adapter drives pagination and detail-link discovery.
            $table->string('adapter')->default('generic');
            $table->boolean('is_enabled')->default(false);
            // user_agent, crawl_delay, timeout, max_pages, listing_path,
            // detail_link_selector, pagination, image settings.
            $table->json('settings')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('robots_checked_at')->nullable();
            $table->json('robots_rules')->nullable();
            $table->timestamps();
        });

        Schema::create('scrape_field_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scrape_source_id')->constrained()->cascadeOnDelete();
            // Where the value lands in the normalized payload, e.g. display_name.
            $table->string('target_field');
            $table->string('selector');
            // text | html | attr:<name> | count
            $table->string('extract')->default('text');
            $table->boolean('multiple')->default(false);
            $table->json('transforms')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['scrape_source_id', 'target_field']);
        });

        Schema::create('scrape_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scrape_source_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('pages_fetched')->default(0);
            $table->unsignedInteger('items_found')->default(0);
            $table->unsignedInteger('items_new')->default(0);
            $table->unsignedInteger('items_updated')->default(0);
            $table->unsignedInteger('items_failed')->default(0);
            $table->json('options')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['scrape_source_id', 'status']);
        });

        Schema::create('scrape_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scrape_source_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scrape_run_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_url', 2048);
            $table->string('external_id')->nullable();
            // Lets a re-run tell "unchanged" from "changed" without diffing JSON.
            $table->string('content_hash', 64)->nullable();
            $table->json('raw')->nullable();
            $table->json('normalized')->nullable();
            $table->json('images')->nullable();
            // pending | approved | rejected | imported | failed
            $table->string('status')->default('pending');
            $table->foreignId('imported_profile_id')->nullable()->constrained('profiles')->nullOnDelete();
            $table->timestamp('imported_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            // One row per profile per source; a re-run updates it in place.
            $table->unique(['scrape_source_id', 'external_id']);
            $table->index(['scrape_source_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrape_items');
        Schema::dropIfExists('scrape_runs');
        Schema::dropIfExists('scrape_field_maps');
        Schema::dropIfExists('scrape_sources');
    }
};
