<?php

namespace App\Services\Scraping;

/**
 * Nothing under eighteen gets in. Ever.
 *
 * Everything else the scraper does is a matter of quality — a wrong price is
 * embarrassing, a stale profile is annoying. This one is not in that category,
 * and it is the reason it is a guard rather than another line in the review
 * queue: a reviewer clicking through fifty profiles at eleven at night is
 * exactly the mechanism that must not be the last line of defence.
 *
 * So the rule is enforced twice, in the two places where data crosses a
 * boundary: when a scraped page becomes a queued item, and when a queued item
 * becomes a profile. Neither can be switched off from the admin, and the
 * minimum cannot be configured below eighteen — a setting may only ever make
 * it stricter.
 */
class AgeGuard
{
    /** The floor. Not a default: a floor. */
    public const MINIMUM = 18;

    /**
     * The effective minimum for a source.
     *
     * A source may raise it — a site that only lists over-21s is welcome to
     * say so — but nothing can lower it.
     */
    public function minimumFor(?int $configured): int
    {
        return max(self::MINIMUM, (int) $configured);
    }

    /**
     * Whether these scraped values must be refused outright.
     *
     * @param  array<string, mixed>  $values
     */
    public function isBlocked(array $values, int $minimum = self::MINIMUM): bool
    {
        $age = $this->age($values);

        // An absent age is not evidence of anything and is left to review.
        // A stated age below the floor is.
        return $age !== null && $age < max(self::MINIMUM, $minimum);
    }

    /** The reason, phrased for whoever finds the blocked row. */
    public function reason(array $values, int $minimum = self::MINIMUM): string
    {
        return sprintf(
            'Zablokováno: uvedený věk %s je pod hranicí %d let. Položka se nedá schválit ani importovat.',
            (string) ($this->age($values) ?? '?'),
            max(self::MINIMUM, $minimum),
        );
    }

    /**
     * The age as a number, from whatever the page and the transforms produced.
     *
     * Deliberately forgiving on the way in and strict on the way out: „19 let",
     * „19" and 19 all mean nineteen, and anything that cannot be read as a
     * plausible age reads as „not stated" rather than as „fine".
     */
    public function age(array $values): ?int
    {
        $raw = $values['age'] ?? null;

        if (is_array($raw)) {
            $raw = $raw[0] ?? null;
        }

        if ($raw === null || $raw === '' || is_bool($raw)) {
            return null;
        }

        if (! preg_match('/\d{1,3}/', (string) $raw, $match)) {
            return null;
        }

        $age = (int) $match[0];

        // Out of any plausible range: a misread field, not an age.
        return $age >= 1 && $age <= 120 ? $age : null;
    }
}
