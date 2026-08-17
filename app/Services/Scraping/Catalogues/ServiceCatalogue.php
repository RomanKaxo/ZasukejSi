<?php

namespace App\Services\Scraping\Catalogues;

use App\Models\ScrapeUnknownValue;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;

class ServiceCatalogue implements FieldCatalogue
{
    public function label(): string
    {
        return 'Služby';
    }

    public function knows(string $value): bool
    {
        $normalized = ScrapeUnknownValue::normalize($value);

        if ($normalized === '') {
            return true;
        }

        return Service::all()->contains(
            fn (Service $service) => ScrapeUnknownValue::normalize(
                (string) $service->getTranslation('name', 'cs')
            ) === $normalized
        );
    }

    public function create(string $value): ?Model
    {
        $normalized = ScrapeUnknownValue::normalize($value);

        // Adopting an existing entry beats duplicating it: two spellings of
        // one service are one service.
        $existing = Service::all()->first(
            fn (Service $service) => ScrapeUnknownValue::normalize(
                (string) $service->getTranslation('name', 'cs')
            ) === $normalized
        );

        return $existing ?: Service::create([
            'name' => ['cs' => $value, 'en' => $value],
            'description' => ['cs' => '', 'en' => ''],
            'sort_order' => (int) Service::max('sort_order') + 1,
            'is_active' => true,
        ]);
    }

    public function canCreate(): bool
    {
        return true;
    }
}
