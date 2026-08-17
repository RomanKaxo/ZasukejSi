<?php

namespace App\Observers;

use App\Models\Profile;
use App\Models\ProfileEditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Records what changed on a profile, and who did it.
 *
 * Deliberately records every edit, not only the ones made in the admin: the
 * question a log answers is „kdo to přepsal", and „provozovatelka sama" is a
 * valid answer to it.
 */
class ProfileEditLogger
{
    /**
     * Columns whose change says nothing worth keeping.
     *
     * `content_blocks` is a page builder payload — recording its before and
     * after would bury every other line in the log.
     */
    private const IGNORED = [
        'updated_at',
        'created_at',
        'content_blocks',
        'last_seen_at',
        'views_count',
    ];

    public function updated(Profile $profile): void
    {
        $changeSet = [];

        foreach ($profile->getChanges() as $field => $to) {
            if (in_array($field, self::IGNORED, true)) {
                continue;
            }

            $from = $profile->getOriginal($field);

            // Casts can make an unchanged value look different (an array
            // re-encoded, a boolean as 1 vs true). Compare what they mean.
            if ($this->same($from, $to)) {
                continue;
            }

            $changeSet[$field] = ['from' => $from, 'to' => $to];
        }

        if ($changeSet === []) {
            return;
        }

        ProfileEditLog::create([
            'profile_id' => $profile->id,
            'user_id' => Auth::id(),
            'change_set' => $changeSet,
        ]);
    }

    private function same(mixed $from, mixed $to): bool
    {
        $normalise = function (mixed $value) {
            if (is_string($value)) {
                $decoded = json_decode($value, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }

            return is_bool($value) ? (int) $value : $value;
        };

        return $normalise($from) == $normalise($to);
    }
}
