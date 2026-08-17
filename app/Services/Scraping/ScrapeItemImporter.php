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

    /**
     * Fields stored inside the profile's `content` JSON.
     *
     * The six below the first five were being scraped and then thrown away —
     * the importer simply had nowhere to put them, so every harvest lost the
     * eye colour, hair colour and length, bust type, pubic hair and travel
     * range it had just fetched.
     */
    public const CONTENT_FIELDS = [
        'card_height_cm',
        'weight_kg',
        'bust_size',
        'nationality',
        'languages',
        'eye_colour',
        'hair_colour',
        'hair_length',
        'bust_type',
        'pubic_hair',
        'travels',
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
                if (array_key_exists($field, $values) && $this->storable($field, $values[$field])) {
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
     * Re-apply what we scraped to a profile that is already imported.
     *
     * A profile imported while the catalogue knew ten services kept ten, even
     * after the missing forty-eight were added — the names were dropped at
     * import time and nothing went back for them. The same held for every
     * detail field the importer had no home for. This is that second pass: it
     * only ever adds, never clears a value somebody has since edited by hand.
     *
     * @return int How many services the profile has afterwards.
     */
    public function resync(ScrapeItem $item): int
    {
        $profile = $item->profile;

        if (! $profile) {
            return 0;
        }

        $values = (array) ($item->normalized ?? []);
        $content = (array) ($profile->content ?? []);
        $changed = false;

        foreach (self::CONTENT_FIELDS as $field) {
            // An edited profile keeps what the editor put there; only genuinely
            // empty fields are filled from the scrape.
            if (! array_key_exists($field, $values) || ($content[$field] ?? null) !== null) {
                continue;
            }

            if (! $this->storable($field, $values[$field])) {
                continue;
            }

            $content[$field] = $values[$field];
            $changed = true;
        }

        if ($changed) {
            $profile->forceFill(['content' => $content])->save();
        }

        $this->attachServices($profile, $values['services'] ?? null);

        return $profile->services()->count();
    }

    /** @deprecated Use {@see resync()}, which also fills the detail fields. */
    public function resyncServices(ScrapeItem $item): int
    {
        return $this->resync($item);
    }

    /**
     * Whether a scraped value is one we have a place for.
     *
     * A field backed by an option list only takes values that list knows —
     * otherwise the admin's select would show a blank where the profile has a
     * value, and the next save would quietly wipe it. What is left out goes
     * into the queue instead and arrives on the next resync, once somebody has
     * added it. Free-text fields (height, nationality, languages) have no list
     * and pass straight through.
     */
    private function storable(string $field, mixed $value): bool
    {
        if (! is_scalar($value) || trim((string) $value) === '') {
            return true;
        }

        $catalogue = app(\App\Services\Scraping\Catalogues\CatalogueRegistry::class)->for($field);

        return $catalogue === null || $catalogue->knows((string) $value);
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
