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

        $this->refuseIfUnderAge($item, $values);

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
                    $content[$field] = $this->storableValue($field, $values[$field]);
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
     * The second of the two age checks.
     *
     * The runner already refuses to queue such an item, so reaching here means
     * the values were edited by hand afterwards — which is precisely why the
     * check is repeated at the boundary where a profile is actually created.
     *
     * @param  array<string, mixed>  $values
     */
    private function refuseIfUnderAge(ScrapeItem $item, array $values): void
    {
        $guard = new AgeGuard();
        $minimum = $guard->minimumFor((int) $item->source?->setting('minimum_age', AgeGuard::MINIMUM));

        if (! $guard->isBlocked($values, $minimum)) {
            return;
        }

        $item->forceFill([
            'status' => ScrapeItem::STATUS_REJECTED,
            'error' => $guard->reason($values, $minimum),
        ])->save();

        throw new RuntimeException($guard->reason($values, $minimum));
    }

    /**
     * Point this scraped item at a profile that already exists.
     *
     * The same woman advertises on three catalogues. Importing each of them
     * made three profiles, and the only cure was somebody deleting two by hand
     * — after which the next run made them again, because nothing recorded
     * that the question had been settled.
     *
     * Attaching says „this page and that profile are the same person". The
     * profile then has several sources: it keeps its own edits, gains whatever
     * the new source fills in that was empty, and — the point of the whole
     * thing — is only reported as vanished once every source has lost it.
     *
     * Never overwrites. A value already on the profile is somebody's decision,
     * and a second catalogue's opinion does not outrank it.
     */
    public function attachTo(ScrapeItem $item, Profile $profile, bool $withImages = true): Profile
    {
        if ($item->imported_profile_id !== null && $item->imported_profile_id !== $profile->id) {
            throw new RuntimeException('Položka už patří k jinému profilu. Nejdřív ji od něj odpojte.');
        }

        $this->refuseIfUnderAge($item, (array) ($item->normalized ?? []));

        $item->forceFill([
            'status' => ScrapeItem::STATUS_IMPORTED,
            'imported_profile_id' => $profile->id,
            'imported_at' => now(),
            'error' => null,
            // Otázka „není to duplicita?" je tímhle zodpovězená.
            'duplicate_profile_id' => null,
            'duplicate_item_id' => null,
            'duplicate_reason' => null,
            'duplicate_checked_at' => now(),
        ])->save();

        $this->resync($item->refresh());

        if ($withImages && $item->images) {
            try {
                // Stahování samo pozná fotky, které profil už má — ať přišly
                // odkudkoli.
                $this->images->download($item, $profile);
            } catch (Throwable $e) {
                $item->forceFill(['error' => 'Fotky: ' . $e->getMessage()])->save();
            }
        }

        return $profile->refresh();
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

            $content[$field] = $this->storableValue($field, $values[$field]);
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

        $registry = app(\App\Services\Scraping\Catalogues\CatalogueRegistry::class);
        $catalogue = $registry->for($field);

        if ($catalogue === null) {
            return true;
        }

        // A list field carries several values in one string („Čeština,
        // Angličtina"). Judging the whole string would drop the lot because of
        // one language we do not know yet, so it is enough that something in it
        // is recognised — `keepKnown()` then trims away the rest.
        if ($registry->isListField($field)) {
            return $this->keepKnown($field, (string) $value) !== '';
        }

        return $catalogue->knows((string) $value);
    }

    /** What actually gets written: a list field keeps only its known part. */
    private function storableValue(string $field, mixed $value): mixed
    {
        $registry = app(\App\Services\Scraping\Catalogues\CatalogueRegistry::class);

        if (! is_scalar($value) || ! $registry->isListField($field) || ! $registry->has($field)) {
            return $value;
        }

        return $this->keepKnown($field, (string) $value);
    }

    /**
     * The recognised part of a list field, in the order it arrived.
     *
     * What is left out is already in the „K doplnění" queue, so it comes back
     * on the next resync once somebody adds it.
     */
    private function keepKnown(string $field, string $value): string
    {
        $catalogue = app(\App\Services\Scraping\Catalogues\CatalogueRegistry::class)->for($field);

        if (! $catalogue) {
            return $value;
        }

        $kept = collect(preg_split('/\s*[,;\/|]\s*/u', $value) ?: [])
            ->map(fn ($part) => trim((string) $part))
            ->filter()
            ->filter(fn (string $part) => $catalogue->knows($part));

        return $kept->implode(', ');
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
