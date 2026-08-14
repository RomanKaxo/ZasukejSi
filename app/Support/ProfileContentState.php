<?php

namespace App\Support;

/**
 * `profiles.content` is an open-ended attribute map shared by two editors: the
 * provider-facing App\Livewire\ProfileForm and the Filament admin form. Neither
 * one knows about every key the other writes.
 *
 * A form submit only carries the keys that form renders, so assigning it
 * straight to the column would silently drop the rest. Merging keeps the map
 * additive: an admin editing a height cannot erase the provider's languages,
 * and neither can erase the featured flag.
 */
class ProfileContentState
{
    /**
     * Merge submitted attributes over the stored ones.
     *
     * Blank submissions clear their key rather than storing an empty string, so
     * the frontend's "value is either true or absent" rule holds and the empty
     * placeholder renders instead of an empty gap.
     *
     * @param  mixed  $stored     Current value of the column.
     * @param  array<string, mixed>  $submitted  Keys present in the form.
     * @return array<string, mixed>|null
     */
    public static function merge(mixed $stored, array $submitted): ?array
    {
        $content = is_array($stored) ? $stored : [];

        foreach ($submitted as $key => $value) {
            if ($value === null || $value === '') {
                unset($content[$key]);
                continue;
            }

            $content[$key] = $value;
        }

        // Booleans that are off arrive as false and are meaningful, but an
        // all-empty map is better stored as NULL than as "{}".
        return $content === [] ? null : $content;
    }
}
