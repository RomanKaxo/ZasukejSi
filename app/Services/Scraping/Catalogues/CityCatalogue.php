<?php

namespace App\Services\Scraping\Catalogues;

use App\Models\City;
use App\Models\ScrapeUnknownValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Towns we know. A profile's region is looked up through this table, so a
 * town that is not in it leaves the profile without one.
 */
class CityCatalogue implements FieldCatalogue
{
    public function knows(string $value): bool
    {
        $normalized = ScrapeUnknownValue::normalize($value);

        if ($normalized === '') {
            return true;
        }

        return City::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($value)])
            ->orWhereRaw('LOWER(name_ascii) = ?', [Str::lower(Str::ascii($value))])
            ->exists();
    }

    public function label(): string
    {
        return 'Města';
    }

    public function create(string $value): ?Model
    {
        if (trim($value) === '') {
            return null;
        }

        return City::firstOrCreate(
            ['name' => $value, 'country_code' => 'CZ'],
            ['name_ascii' => Str::ascii($value)],
        );
    }

    public function canCreate(): bool
    {
        return true;
    }
}
