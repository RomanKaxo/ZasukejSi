<?php

namespace App\Filament\Resources\Profiles\Concerns;

use App\Models\Profile;

/**
 * Moves the service price list between the form and the profile_service pivot.
 *
 * `servicePrices` is not a column, so Filament cannot hydrate or persist it on
 * its own: it is read out of the pivot when the form fills, and written back
 * after the record is saved.
 */
trait SyncsServicePrices
{
    /** Held between mutateFormDataBeforeSave() and the save hook. */
    protected array $servicePriceRows = [];

    /** @return array<int, array{service_id: int, prices: array, note: ?string}> */
    protected function readServicePrices(Profile $profile): array
    {
        return $profile->services()
            ->get()
            ->map(function ($service) {
                $prices = $service->pivot->prices;

                if (is_string($prices)) {
                    $prices = json_decode($prices, true);
                }

                return [
                    'service_id' => $service->id,
                    'prices' => is_array($prices) ? $prices : [],
                    'note' => $service->pivot->note,
                ];
            })
            ->values()
            ->all();
    }

    protected function extractServicePrices(array $data): array
    {
        $this->servicePriceRows = $data['servicePrices'] ?? [];

        unset($data['servicePrices']);

        return $data;
    }

    protected function writeServicePrices(Profile $profile): void
    {
        $payload = [];

        foreach ($this->servicePriceRows as $row) {
            $serviceId = (int) ($row['service_id'] ?? 0);

            if ($serviceId === 0) {
                continue;
            }

            // Blank inputs are dropped rather than stored as zero — "not
            // priced" and "free" are different claims.
            $prices = collect($row['prices'] ?? [])
                ->filter(fn ($amount) => $amount !== null && $amount !== '')
                ->map(fn ($amount) => (float) $amount)
                ->all();

            $payload[$serviceId] = [
                'prices' => $prices === [] ? null : json_encode($prices),
                'note' => $row['note'] ?? null,
            ];
        }

        // sync() also removes services no longer in the list, which is what
        // deleting a row in the repeater is meant to do.
        $profile->services()->sync($payload);
    }
}
