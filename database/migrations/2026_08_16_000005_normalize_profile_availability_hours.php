<?php

use App\Support\Availability;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One shape for opening hours.
 *
 * Three writers had produced three shapes in this column: the services manager
 * wrote {always_online, schedule}, the member profile form a flat list of
 * strings, and the admin a key/value map. Reading meant guessing, and the
 * admin showed "schedule" as a literal key with the values dumped beside it.
 *
 * Everything is converted to the services manager's shape, which is the only
 * one that can express a different range per day. Rows that carry nothing
 * recognisable become an empty schedule rather than being left as they are —
 * a half-parsed range is worse than none.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('profiles')
            ->whereNotNull('availability_hours')
            ->orderBy('id')
            ->chunkById(200, function ($profiles) {
                foreach ($profiles as $profile) {
                    $decoded = json_decode((string) $profile->availability_hours, true);

                    if (! is_array($decoded)) {
                        continue;
                    }

                    $normalized = Availability::normalize($decoded);

                    DB::table('profiles')
                        ->where('id', $profile->id)
                        ->update(['availability_hours' => json_encode($normalized)]);
                }
            });
    }

    public function down(): void
    {
        // The old shapes carried no more information than the new one, so
        // there is nothing to restore them from.
    }
};
