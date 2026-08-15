<?php

namespace App\Support;

/**
 * The one definition of a profile's opening hours.
 *
 * Three places have written this column in three different shapes: the member
 * services manager as {always_online, schedule: {day => {from, to}}}, the
 * member profile form as a flat list of strings, and the admin as a key/value
 * map. Reading it therefore meant guessing, and the admin ended up showing
 * "schedule" as a literal key with the values dumped beside it.
 *
 * The services manager's shape is the canonical one — it is the only one that
 * can express a different range per day. Everything now normalises to it.
 */
class Availability
{
    /** Day keys, in the order a week is read. Stable and locale independent. */
    public const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    /**
     * @return array<string, string> day key => translated name
     *
     * Spelled out rather than built from the day key: a composed key cannot be
     * checked by `translations:audit`, which is the point of having it.
     */
    public static function dayLabels(): array
    {
        return [
            'monday' => __('profiles.days.monday'),
            'tuesday' => __('profiles.days.tuesday'),
            'wednesday' => __('profiles.days.wednesday'),
            'thursday' => __('profiles.days.thursday'),
            'friday' => __('profiles.days.friday'),
            'saturday' => __('profiles.days.saturday'),
            'sunday' => __('profiles.days.sunday'),
        ];
    }

    public static function dayLabel(string $day): string
    {
        return self::dayLabels()[$day] ?? ucfirst($day);
    }

    /**
     * Coerce any shape this column has been written in into the canonical one.
     *
     * @return array{always_online: bool, schedule: array<string, array{from: string, to: string}>}
     */
    public static function normalize(mixed $value): array
    {
        if (! is_array($value) || $value === []) {
            return ['always_online' => false, 'schedule' => []];
        }

        // Already canonical.
        if (array_key_exists('schedule', $value) || array_key_exists('always_online', $value)) {
            return [
                'always_online' => (bool) ($value['always_online'] ?? false),
                'schedule' => self::normalizeSchedule($value['schedule'] ?? []),
            ];
        }

        // A day => hours map, or a list of "Pondělí 9:00-17:00" strings.
        return ['always_online' => false, 'schedule' => self::normalizeSchedule($value)];
    }

    /** @return array<string, array{from: string, to: string}> */
    private static function normalizeSchedule(mixed $schedule): array
    {
        if (! is_array($schedule)) {
            return [];
        }

        $normalized = [];

        foreach ($schedule as $key => $entry) {
            // List entry: the day is inside the text.
            if (is_int($key)) {
                if (! is_string($entry)) {
                    continue;
                }

                $day = self::matchDay($entry);
                $range = self::parseRange($entry);

                if ($day !== null && $range !== null) {
                    $normalized[$day] = $range;
                }

                continue;
            }

            $day = self::matchDay((string) $key);

            if ($day === null) {
                continue;
            }

            $range = is_array($entry)
                ? self::rangeFromPair($entry)
                : self::parseRange((string) $entry);

            if ($range !== null) {
                $normalized[$day] = $range;
            }
        }

        // Keep the week in order rather than in the order it was written.
        $ordered = [];
        foreach (self::DAYS as $day) {
            if (isset($normalized[$day])) {
                $ordered[$day] = $normalized[$day];
            }
        }

        return $ordered;
    }

    /** @return array{from: string, to: string}|null */
    private static function rangeFromPair(array $entry): ?array
    {
        $from = $entry['from'] ?? $entry[0] ?? null;
        $to = $entry['to'] ?? $entry[1] ?? null;

        if (! is_scalar($from) || ! is_scalar($to)) {
            return null;
        }

        $from = trim((string) $from);
        $to = trim((string) $to);

        return ($from === '' || $to === '') ? null : ['from' => $from, 'to' => $to];
    }

    /** @return array{from: string, to: string}|null */
    private static function parseRange(string $text): ?array
    {
        if (preg_match('/(\d{1,2}(?::\d{2})?)\s*[-–—]\s*(\d{1,2}(?::\d{2})?)/u', $text, $m)) {
            return ['from' => self::padTime($m[1]), 'to' => self::padTime($m[2])];
        }

        return null;
    }

    private static function padTime(string $time): string
    {
        return str_contains($time, ':') ? $time : $time . ':00';
    }

    /** Recognise a day from an English key or a Czech/English label. */
    private static function matchDay(string $text): ?string
    {
        $needle = mb_strtolower(trim($text));

        foreach (self::DAYS as $day) {
            if (str_starts_with($needle, $day)) {
                return $day;
            }
        }

        $czech = [
            'monday' => ['pondělí', 'pondeli', 'po'],
            'tuesday' => ['úterý', 'utery', 'út', 'ut'],
            'wednesday' => ['středa', 'streda', 'st'],
            'thursday' => ['čtvrtek', 'ctvrtek', 'čt', 'ct'],
            'friday' => ['pátek', 'patek', 'pá', 'pa'],
            'saturday' => ['sobota', 'so'],
            'sunday' => ['neděle', 'nedele', 'ne'],
        ];

        foreach ($czech as $day => $aliases) {
            foreach ($aliases as $alias) {
                if (str_starts_with($needle, $alias)) {
                    return $day;
                }
            }
        }

        return null;
    }

    /**
     * Day-by-day lines for display, e.g. ["Pondělí" => "9:00 – 17:00"].
     *
     * @return array<string, string>
     */
    public static function lines(mixed $value): array
    {
        $normalized = self::normalize($value);
        $lines = [];

        foreach ($normalized['schedule'] as $day => $range) {
            $lines[self::dayLabel($day)] = $range['from'] . ' – ' . $range['to'];
        }

        return $lines;
    }

    public static function isAlwaysOnline(mixed $value): bool
    {
        return self::normalize($value)['always_online'];
    }
}
