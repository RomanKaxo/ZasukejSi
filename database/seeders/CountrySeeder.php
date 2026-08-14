<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * The countries the site listed before this table existed. They were spread
     * across three hardcoded arrays (CountryProfiles, SearchProfiles and the
     * English sidebar component); seeding them keeps the public lists looking
     * exactly the same, minus the duplicates.
     *
     * @var array<int, string>
     */
    private const PREVIOUSLY_LISTED = [
        'AL', // Albánie
        'AD', // Andorra
        'AM', // Arménie
        'BE', // Belgie
        'BY', // Bělorusko
        'BA', // Bosna a Hercegovina
        'BG', // Bulharsko
        'ME', // Černá Hora
        'CZ', // Česká republika
    ];

    public function run(): void
    {
        // Anything that actually holds profiles must be listed, otherwise those
        // profiles would be unreachable through country navigation.
        $withProfiles = DB::table('profiles')
            ->whereNotNull('country_code')
            ->distinct()
            ->pluck('country_code')
            ->map(fn ($code) => strtoupper((string) $code))
            ->all();

        $codes = collect(self::PREVIOUSLY_LISTED)
            ->merge($withProfiles)
            ->filter()
            ->unique()
            ->values();

        // Czech alphabetical order, matching how the original hardcoded list was
        // sorted (Albánie, Andorra, Arménie, Belgie, Bělorusko, …).
        $sorted = $this->sortByCzechName($codes->all());

        foreach ($sorted as $index => $code) {
            Country::updateOrCreate(
                ['code' => $code],
                [
                    'sort_order' => ($index + 1) * 10,
                    // Only set on create — an admin may have hidden a country on
                    // purpose, and re-seeding must not undo that.
                    'is_visible' => Country::where('code', $code)->value('is_visible') ?? true,
                ]
            );
        }
    }

    /**
     * @param  array<int, string>  $codes
     * @return array<int, string>
     */
    private function sortByCzechName(array $codes): array
    {
        $names = [];
        foreach ($codes as $code) {
            $names[$code] = Country::isoName($code);
        }

        if (class_exists(\Collator::class)) {
            $collator = new \Collator('cs_CZ');
            uasort($names, fn ($a, $b) => $collator->compare($a, $b));
        } else {
            asort($names, SORT_NATURAL | SORT_FLAG_CASE);
        }

        return array_keys($names);
    }
}
