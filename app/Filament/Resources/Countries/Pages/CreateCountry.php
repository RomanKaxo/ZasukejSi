<?php

namespace App\Filament\Resources\Countries\Pages;

use App\Filament\Resources\Countries\CountryResource;
use App\Services\CountryStatsService;
use Filament\Resources\Pages\CreateRecord;

class CreateCountry extends CreateRecord
{
    protected static string $resource = CountryResource::class;

    /**
     * `name_override` is translatable; the form edits one locale at a time.
     * An empty value must stay empty rather than becoming an empty translation,
     * so the model falls back to the standard ISO name.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['name_override'] = filled($data['name_override'] ?? null)
            ? [app()->getLocale() => $data['name_override']]
            : null;

        return $data;
    }

    protected function afterCreate(): void
    {
        CountryStatsService::flush();
    }
}
