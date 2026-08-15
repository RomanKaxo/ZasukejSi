<?php

namespace App\Services\Scraping;

use App\Models\Profile;
use App\Models\ScrapeItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Turns an approved scrape item into a Profile.
 *
 * Deliberately separate from the run: an item has to be approved by a person
 * first, and what is created here is never public. It lands as a draft for
 * the same review the site's own submissions go through.
 */
class ScrapeItemImporter
{
    /** Fields the importer knows how to place on a Profile. */
    public const DIRECT_FIELDS = [
        'display_name',
        'age',
        'city',
        'address',
        'about',
        'country_code',
    ];

    /** Fields stored inside the profile's `content` JSON. */
    public const CONTENT_FIELDS = [
        'card_height_cm',
        'weight_kg',
        'bust_size',
        'nationality',
        'languages',
    ];

    public function __construct(private readonly ScrapeImageDownloader $images)
    {
    }

    public function import(ScrapeItem $item, bool $withImages = true): Profile
    {
        if ($item->status === ScrapeItem::STATUS_IMPORTED && $item->imported_profile_id) {
            throw new RuntimeException('Položka už byla importována.');
        }

        if ($item->status !== ScrapeItem::STATUS_APPROVED) {
            throw new RuntimeException('Importovat lze jen schválenou položku.');
        }

        $values = $item->normalized ?? [];

        if (($values['display_name'] ?? null) === null) {
            throw new RuntimeException('Chybí jméno profilu.');
        }

        $profile = DB::transaction(function () use ($item, $values) {
            $attributes = [];

            foreach (self::DIRECT_FIELDS as $field) {
                if (array_key_exists($field, $values)) {
                    $attributes[$field] = $values[$field];
                }
            }

            $content = [];
            foreach (self::CONTENT_FIELDS as $field) {
                if (array_key_exists($field, $values)) {
                    $content[$field] = $values[$field];
                }
            }

            if ($content !== []) {
                $attributes['content'] = $content;
            }

            // Never public, never approved: an imported row is a draft that
            // still has to be checked and published by hand.
            $attributes['status'] = 'pending';
            $attributes['is_public'] = false;
            $attributes['user_id'] = null;

            if (isset($attributes['country_code'])) {
                $attributes['country_code'] = Str::upper((string) $attributes['country_code']);
            }

            $profile = Profile::create($attributes);

            $item->forceFill([
                'status' => ScrapeItem::STATUS_IMPORTED,
                'imported_profile_id' => $profile->id,
                'imported_at' => now(),
                'error' => null,
            ])->save();

            $this->attachServices($profile, $values['services'] ?? null);

            return $profile;
        });

        if ($withImages && $item->images) {
            try {
                $this->images->download($item, $profile);
            } catch (Throwable $e) {
                // The profile is already saved; a failed photo run is worth
                // recording but must not roll the import back.
                $item->forceFill(['error' => 'Fotky: ' . $e->getMessage()])->save();
            }
        }

        return $profile;
    }

    /**
     * Link scraped service names to the services we already offer.
     *
     * Only names that already exist are attached — a scraped list must not be
     * able to invent entries in our own service catalogue. Matching is on the
     * Czech name, case-insensitively.
     *
     * @param  array<int, string>|null  $names
     */
    private function attachServices(Profile $profile, ?array $names): void
    {
        if (! $names) {
            return;
        }

        $wanted = collect($names)
            ->map(fn ($name) => Str::lower(trim((string) $name)))
            ->filter()
            ->unique();

        if ($wanted->isEmpty()) {
            return;
        }

        $ids = \App\Models\Service::all()
            ->filter(fn ($service) => $wanted->contains(
                Str::lower((string) $service->getTranslation('name', 'cs'))
            ))
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            $profile->services()->sync($ids);
        }
    }
}
