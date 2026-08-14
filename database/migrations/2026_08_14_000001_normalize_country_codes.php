<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * `profiles.country_code` accumulated mixed casing over time — measured values
 * were ["AT","CZ","DE","PL","SK","cz","us"] — while `cities.country_code` is
 * always uppercase (it comes from worldcities.csv).
 *
 * Every country/region feature joins those two columns:
 *   - CountryProfiles aggregates via whereColumn('cities.country_code', 'profiles.country_code')
 *   - Profile::getRegionAttribute() looks up cities by the profile's code
 *   - ProfileList::applyRegionFilter() correlates the same two columns
 *
 * On MySQL a case-insensitive collation papered over the mismatch; on SQLite
 * (and on any binary collation) the lowercase rows silently dropped out of
 * every country listing. Normalising to uppercase makes the invariant real
 * rather than collation-dependent; Profile::setCountryCodeAttribute() keeps it
 * true for anything written from now on.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('profiles')
            ->whereNotNull('country_code')
            ->update(['country_code' => DB::raw('UPPER(country_code)')]);

        // Defensive: the CSV import is uppercase today, but the join is only
        // sound if both sides are guaranteed.
        DB::table('cities')
            ->whereNotNull('country_code')
            ->update(['country_code' => DB::raw('UPPER(country_code)')]);
    }

    public function down(): void
    {
        // Deliberately irreversible: the previous state was inconsistent casing,
        // which is not a state worth restoring and cannot be reconstructed.
    }
};
