<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\ProfileView;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Demo návštěvnost, aby graf v administraci měl co kreslit.
 *
 * Bez ní ukazuje sekce „Zobrazení profilů" u každého profilu „bez zobrazení"
 * a nedá se na ní nic ověřit — ani řazení podle nejvíc zobrazovaných, ani
 * přepínání období.
 *
 * Rozprostřeno přes celý rok s rostoucím trendem a víkendovými špičkami, aby
 * graf měl tvar, ne rovnou čáru. Existující data nechává být: pouští se jen
 * na profilech, které zatím žádné zobrazení nemají.
 */
class ProfileViewDemoSeeder extends Seeder
{
    /** Kolik zobrazení denně dostane nejsledovanější profil v posledním měsíci. */
    private const PEAK_PER_DAY = 14;

    /**
     * Nad tímhle počtem se profil nechá být.
     *
     * Původně to bylo „nemá ani jedno zobrazení", jenže jiný seeder jich
     * pár rozdal každému profilu, takže se přeskočily úplně všechny a graf
     * zůstal prázdný. Profil se skutečným provozem má víc než tohle.
     */
    private const LEAVE_ALONE_ABOVE = 60;

    public function run(): void
    {
        // Filtrováno v PHP, ne `having` nad aliasem `withCount` — ten se
        // v SQLite tiše nechytne a seeder pak neudělá nic.
        $profiles = Profile::query()
            ->withCount('views')
            ->orderBy('id')
            ->get()
            ->filter(fn (Profile $profile) => $profile->views_count <= self::LEAVE_ALONE_ABOVE)
            ->values();

        if ($profiles->isEmpty()) {
            return;
        }

        $rows = [];

        foreach ($profiles->values() as $index => $profile) {
            // Sestupná oblíbenost, ať má řazení co řadit.
            $popularity = max(0.08, 1 - ($index / max(1, $profiles->count())));

            foreach (range(0, 364) as $daysAgo) {
                $date = now()->subDays(364 - $daysAgo);

                // Roste s časem: starší měsíce jsou slabší než ty poslední.
                $trend = 0.25 + (0.75 * ($daysAgo / 364));

                // Pátek a sobota jsou silnější.
                $weekend = in_array($date->dayOfWeek, [5, 6], true) ? 1.6 : 1.0;

                $count = (int) round(self::PEAK_PER_DAY * $popularity * $trend * $weekend);

                // Trocha nepravidelnosti, ať to nevypadá jako funkce.
                $count = max(0, $count + random_int(-2, 2));

                for ($i = 0; $i < $count; $i++) {
                    $rows[] = [
                        'profile_id' => $profile->id,
                        'viewer_id' => null,
                        'type' => random_int(1, 6) === 1
                            ? ProfileView::TYPE_CLICK
                            : ProfileView::TYPE_IMPRESSION,
                        'viewed_date' => $date->toDateString(),
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                }

                // Po tisícovkách, ať se to vejde do paměti i do limitu vazeb.
                if (count($rows) >= 1000) {
                    DB::table('profile_views')->insert($rows);
                    $rows = [];
                }
            }
        }

        if ($rows !== []) {
            DB::table('profile_views')->insert($rows);
        }
    }
}
