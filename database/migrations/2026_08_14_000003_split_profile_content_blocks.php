<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Two different features were writing to `profiles.content`:
 *
 *   1. App\Livewire\ProfileForm (provider-facing) stores an associative map of
 *      attributes — card_height_cm, card_location, weight_kg, bust_size,
 *      nationality, languages, has_whatsapp, has_telegram, global_currency,
 *      is_showcase.
 *
 *   2. The Filament admin pointed a block-builder field (BlocksInput) at the
 *      same column, which expects a *list* of {type, data} blocks.
 *
 * The consequences were worse than overwriting: Filament's Builder iterates the
 * raw state and type-hints each item as an array, so opening the edit page for
 * any profile that had attributes filled in threw
 *
 *     Argument #1 ($itemData) must be of type array, int given
 *
 * i.e. every profile with a height could not be edited in the admin at all, and
 * saving one that could would have destroyed the provider's attributes.
 *
 * This gives the block builder its own column and moves any list-shaped data
 * across, leaving `content` as the attribute map it is everywhere else in the
 * codebase (Profile::getHeightAttribute() and friends, profile-card,
 * profile-detail, ProfileList's featured ordering).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->json('content_blocks')->nullable()->after('content');
        });

        DB::table('profiles')
            ->select('id', 'content')
            ->whereNotNull('content')
            ->orderBy('id')
            ->chunk(200, function ($profiles) {
                foreach ($profiles as $profile) {
                    $decoded = json_decode((string) $profile->content, true);

                    // Only a list of blocks belongs in the new column; the
                    // attribute map stays where every reader expects it.
                    if (! is_array($decoded) || $decoded === [] || ! array_is_list($decoded)) {
                        continue;
                    }

                    DB::table('profiles')->where('id', $profile->id)->update([
                        'content_blocks' => json_encode($decoded),
                        'content' => null,
                    ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('profiles')
            ->whereNotNull('content_blocks')
            ->orderBy('id')
            ->chunk(200, function ($profiles) {
                foreach ($profiles as $profile) {
                    // Only restore where it cannot clobber an attribute map.
                    if ($profile->content === null) {
                        DB::table('profiles')->where('id', $profile->id)->update([
                            'content' => $profile->content_blocks,
                        ]);
                    }
                }
            });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('content_blocks');
        });
    }
};
