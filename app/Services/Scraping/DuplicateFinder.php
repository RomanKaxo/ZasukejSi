<?php

namespace App\Services\Scraping;

use App\Models\Profile;
use App\Models\ScrapeItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Recognises that a scraped item is somebody we already have.
 *
 * The runner only ever knew about a repeat of itself — the same
 * `external_id` from the same source. The same woman advertised on two sites,
 * or already present as a profile here, was invisible, and importing her
 * created a second profile.
 *
 * Nothing here decides anything. It reports candidates with a reason, and a
 * person still says yes or no.
 */
class DuplicateFinder
{
    /** Ordered strongest first; the first hit wins. */
    public const REASON_PHONE = 'phone';
    public const REASON_URL = 'url';
    public const REASON_NAME_CITY = 'name_city';
    public const REASON_NAME = 'name';

    /**
     * @return array{profile: ?Profile, item: ?ScrapeItem, reason: ?string}
     */
    public function find(ScrapeItem $item): array
    {
        $name = self::normalizeName((string) $item->value('display_name'));
        $city = self::normalizeName((string) $item->value('city'));
        $phones = self::phonesOf((array) ($item->normalized ?? []));

        // A profile is a stronger answer than another queue item: it already
        // exists on the site, so importing again is the actual mistake.
        if ($match = $this->matchProfile($name, $city, $phones, $item)) {
            return $match;
        }

        if ($match = $this->matchItem($name, $city, $phones, $item)) {
            return $match;
        }

        return ['profile' => null, 'item' => null, 'reason' => null];
    }

    /**
     * Store what was found on the item.
     *
     * A previous verdict is always cleared first — an item whose name was
     * corrected must not keep pointing at the profile the old name matched.
     */
    public function check(ScrapeItem $item): ScrapeItem
    {
        $found = $this->find($item);

        $item->forceFill([
            'duplicate_profile_id' => $found['profile']?->id,
            'duplicate_item_id' => $found['item']?->id,
            'duplicate_reason' => $found['reason'],
            'duplicate_checked_at' => now(),
        ])->save();

        return $item;
    }

    /** @param  array<int, string>  $phones */
    private function matchProfile(string $name, string $city, array $phones, ScrapeItem $item): ?array
    {
        // The phone is now a column, so the strongest signal is one indexed
        // query instead of loading every profile and comparing in PHP.
        if ($phones !== []) {
            $byPhone = Profile::query()
                ->when($item->imported_profile_id, fn ($q) => $q->whereKeyNot($item->imported_profile_id))
                ->whereNotNull('phone')
                ->get(['id', 'phone'])
                ->first(fn (Profile $profile) => array_intersect($phones, self::phonesOf([$profile->phone])) !== []);

            if ($byPhone) {
                return ['profile' => $byPhone, 'item' => null, 'reason' => self::REASON_PHONE];
            }
        }

        if ($name === '') {
            return null;
        }

        $candidates = Profile::query()
            ->when($item->imported_profile_id, fn ($q) => $q->whereKeyNot($item->imported_profile_id))
            ->get(['id', 'display_name', 'city']);

        $sameName = $candidates->filter(
            fn (Profile $profile) => self::normalizeName(self::displayNameOf($profile)) === $name
        );

        if ($sameName->isEmpty()) {
            return null;
        }

        // Same name in the same town is a much stronger claim than the name
        // alone — plenty of women share a working name.
        $sameCity = $city === ''
            ? null
            : $sameName->first(fn (Profile $profile) => self::normalizeName((string) $profile->city) === $city);

        return $sameCity
            ? ['profile' => $sameCity, 'item' => null, 'reason' => self::REASON_NAME_CITY]
            : ['profile' => $sameName->first(), 'item' => null, 'reason' => self::REASON_NAME];
    }

    /** @param  array<int, string>  $phones */
    private function matchItem(string $name, string $city, array $phones, ScrapeItem $item): ?array
    {
        if ($name === '' && $phones === []) {
            return null;
        }

        $candidates = ScrapeItem::query()
            ->whereKeyNot($item->getKey())
            // A rejected item is a decision already made; re-raising it as a
            // duplicate would drag it back into the reviewer's way.
            ->whereNotIn('status', [ScrapeItem::STATUS_REJECTED])
            ->get(['id', 'scrape_source_id', 'normalized', 'source_url', 'status']);

        foreach ($candidates as $candidate) {
            $candidatePhones = self::phonesOf((array) ($candidate->normalized ?? []));

            if ($phones !== [] && array_intersect($phones, $candidatePhones) !== []) {
                return ['profile' => null, 'item' => $candidate, 'reason' => self::REASON_PHONE];
            }
        }

        if ($name === '') {
            return null;
        }

        $sameName = $candidates->filter(
            fn (ScrapeItem $candidate) => self::normalizeName((string) ($candidate->normalized['display_name'] ?? '')) === $name
        );

        if ($sameName->isEmpty()) {
            return null;
        }

        $sameCity = $city === ''
            ? null
            : $sameName->first(
                fn (ScrapeItem $candidate) => self::normalizeName((string) ($candidate->normalized['city'] ?? '')) === $city
            );

        return $sameCity
            ? ['profile' => null, 'item' => $sameCity, 'reason' => self::REASON_NAME_CITY]
            : ['profile' => null, 'item' => $sameName->first(), 'reason' => self::REASON_NAME];
    }

    /**
     * Diacritics, case and spacing dropped: "Anička" and "ANICKA" are the same
     * working name, and the two sites will not spell it the same way.
     */
    public static function normalizeName(string $value): string
    {
        $value = Str::of($value)->ascii()->lower()->toString();
        $value = preg_replace('/[^a-z0-9]+/', '', $value) ?? '';

        return $value;
    }

    /**
     * Every phone-looking value in a bag of scraped or stored contacts.
     *
     * Reduced to its last nine digits, so +420 777 123 456, 00420777123456 and
     * 777123456 are one number.
     *
     * @param  array<mixed>  $values
     * @return array<int, string>
     */
    public static function phonesOf(array $values): array
    {
        $phones = [];

        array_walk_recursive($values, function ($value) use (&$phones) {
            if (! is_scalar($value)) {
                return;
            }

            $digits = preg_replace('/\D+/', '', (string) $value) ?? '';

            // Shorter than nine digits is a house number or a price, not a
            // phone; longer gets trimmed to the national part.
            if (strlen($digits) >= 9) {
                $phones[] = substr($digits, -9);
            }
        });

        return array_values(array_unique($phones));
    }

    private static function displayNameOf(Profile $profile): string
    {
        $name = $profile->display_name;

        if (is_array($name)) {
            return (string) (reset($name) ?: '');
        }

        return (string) $name;
    }

    /** @return Collection<int, string> */
    public static function reasonLabels(): Collection
    {
        return collect([
            self::REASON_PHONE => 'shodný telefon',
            self::REASON_URL => 'shodná adresa',
            self::REASON_NAME_CITY => 'shodné jméno i město',
            self::REASON_NAME => 'shodné jméno',
        ]);
    }
}
