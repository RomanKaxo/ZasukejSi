<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert legacy profile price data to the structure the app actually uses.
     *
     * `profiles.local_prices` / `global_prices` are edited by App\Livewire\ProfileForm
     * and rendered by components/profile-detail.blade.php, both of which expect a
     * LIST of rows:
     *
     *     [ ['time_hours' => 1, 'incall_price' => 2000, 'outcall_price' => null], ... ]
     *
     * Legacy rows instead stored a flat slot => price map:
     *
     *     ['30min' => 2659, '1hour' => 208, '2hours' => 3905, 'overnight' => 1956]
     *
     * With that shape the detail page filtered every row out (so prices never
     * displayed) and the settings form failed validation on
     * `local_prices.*.time_hours`, which made it impossible for a provider to save
     * her profile at all.
     */
    private const SLOT_HOURS = [
        '30min' => 0.5,
        '1hour' => 1,
        '2hours' => 2,
        '3hours' => 3,
        'overnight' => 12,
    ];

    public function up(): void
    {
        foreach (['local_prices', 'global_prices'] as $column) {
            DB::table('profiles')
                ->select('id', $column)
                ->whereNotNull($column)
                ->orderBy('id')
                ->chunkById(100, function ($rows) use ($column) {
                    foreach ($rows as $row) {
                        $decoded = json_decode($row->{$column} ?? 'null', true);

                        if (! is_array($decoded) || $decoded === [] || array_is_list($decoded)) {
                            continue; // already in the canonical shape (or empty)
                        }

                        $converted = [];

                        foreach ($decoded as $slot => $price) {
                            // Anything already shaped like a row is kept as-is.
                            if (is_array($price)) {
                                $converted[] = $price;
                                continue;
                            }

                            if (! is_numeric($price)) {
                                continue;
                            }

                            $converted[] = [
                                'time_hours' => self::SLOT_HOURS[$slot] ?? null,
                                'incall_price' => (int) $price,
                                'outcall_price' => null,
                            ];
                        }

                        // Drop rows we could not map to a duration — the form
                        // requires time_hours, so a null would block saving again.
                        $converted = array_values(array_filter(
                            $converted,
                            fn ($row) => ($row['time_hours'] ?? null) !== null
                        ));

                        // Sort by duration so the table reads naturally.
                        usort($converted, fn ($a, $b) => $a['time_hours'] <=> $b['time_hours']);

                        DB::table('profiles')
                            ->where('id', $row->id)
                            ->update([$column => json_encode($converted ?: null)]);
                    }
                });
        }
    }

    /**
     * Not reversible: the legacy slot names carry less information than the
     * numeric durations they were converted into.
     */
    public function down(): void
    {
        // no-op
    }
};
