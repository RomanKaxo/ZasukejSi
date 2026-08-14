<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Editable translations.
 *
 * Every user-facing string on the site goes through `__()`, but the values
 * lived only in `lang/*.php` — so the hero copy, the advert block, the eco
 * badge and every button label could only be changed by editing files and
 * deploying. The admin had no way to touch them.
 *
 * A row here overrides the corresponding file value for one locale. Files stay
 * the source of defaults (and the thing `translations:audit` checks), the
 * database is the override layer on top — see App\Services\DatabaseTranslationLoader.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();

            $table->string('locale', 8);

            // The file the key lives in: "front", "profiles", … For JSON
            // translations (__('Some sentence')) this is the reserved "*".
            $table->string('group', 64);

            // Dot path inside the file, e.g. "nav.home" or "landing.advert.title".
            $table->string('key', 191);

            $table->text('value')->nullable();

            // The value shipped in lang/, kept so the admin can see what they
            // are overriding and revert to it.
            $table->text('default_value')->nullable();

            $table->timestamps();

            $table->unique(['locale', 'group', 'key']);
            // The loader fetches a whole group for one locale at a time.
            $table->index(['locale', 'group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
