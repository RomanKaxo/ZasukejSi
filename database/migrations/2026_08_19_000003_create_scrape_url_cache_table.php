<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What we already have from a URL, so we do not ask for it again.
 *
 * Every scheduled run re-downloaded every detail page in full, even when not
 * a byte had changed since the last one. On a site with a few hundred profiles
 * that is a few hundred pointless megabytes a day — ours to pay for and
 * theirs to serve.
 *
 * With an ETag or a Last-Modified we ask conditionally and a site that has
 * nothing new answers 304 with an empty body.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scrape_url_cache', function (Blueprint $table) {
            $table->id();

            $table->foreignId('scrape_source_id')->constrained()->cascadeOnDelete();

            // The URL itself is too long to index on MySQL; its hash is not.
            $table->char('url_hash', 40);
            $table->text('url');

            $table->string('etag')->nullable();
            $table->string('last_modified')->nullable();

            // What the body hashed to, so a site that sends neither header
            // still tells us whether anything moved.
            $table->char('content_hash', 40)->nullable();

            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->unique(['scrape_source_id', 'url_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrape_url_cache');
    }
};
