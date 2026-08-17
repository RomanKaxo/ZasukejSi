<?php

namespace App\Services\Scraping;

use Illuminate\Support\Str;

/**
 * Named value transforms, applied in order to whatever a selector produced.
 *
 * They live in a registry rather than in code per source, so a new site is
 * configured from the admin instead of being written.
 */
class Transformers
{
    /**
     * Human labels for the admin picker. The key is what gets stored.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            'trim' => 'Oříznout mezery',
            'collapse_whitespace' => 'Sloučit mezery a odřádkování',
            'lower' => 'Malá písmena',
            'upper' => 'Velká písmena',
            'int' => 'Číslo (celé)',
            'float' => 'Číslo (desetinné)',
            'digits' => 'Ponechat jen číslice',
            'strip_tags' => 'Odstranit HTML značky',
            'absolute_url' => 'Doplnit na absolutní URL',
            'first' => 'První hodnota',
            'compact' => 'Zahodit prázdné hodnoty',
            'unique' => 'Odstranit duplicity',
            'boolean' => 'Ano/ne',
            'strip_invisible' => 'Odstranit neviditelné znaky',
            'reject' => 'Vyřadit hodnoty odpovídající vzoru',
        ];
    }

    /**
     * @param  array<int, string|array{0: string, 1: mixed}>  $transforms
     */
    public function apply(mixed $value, array $transforms, array $context = []): mixed
    {
        foreach ($transforms as $transform) {
            // Either "trim" or ["regex", "/pattern/"] for the ones that need
            // an argument.
            [$name, $argument] = is_array($transform)
                ? [$transform[0] ?? '', $transform[1] ?? null]
                : [$transform, null];

            $value = $this->applyOne($value, (string) $name, $argument, $context);
        }

        return $value;
    }

    private function applyOne(mixed $value, string $name, mixed $argument, array $context): mixed
    {
        // List-wide transforms run before the per-value ones.
        if ($name === 'first') {
            return is_array($value) ? ($value[0] ?? null) : $value;
        }

        if ($name === 'unique') {
            return is_array($value) ? array_values(array_unique($value)) : $value;
        }

        // Runs before `first` so a per-element regex that matched nothing on
        // most rows does not make `first` return null.
        if ($name === 'compact') {
            return is_array($value)
                ? array_values(array_filter($value, fn ($item) => $item !== null && $item !== '' && $item !== []))
                : $value;
        }

        // Drops whole entries rather than rewriting them, so it has to run on
        // the list before the per-value map below.
        //
        // The services table on eurogirlsescort.cz shares its markup with the
        // opening-hours and price tables, so one selector returns services,
        // weekday names and "12 Hodiny" side by side.
        if ($name === 'reject') {
            $pattern = (string) $argument;

            if ($pattern === '') {
                return $value;
            }

            $matches = fn ($item) => is_scalar($item)
                && @preg_match($pattern, (string) $item) === 1;

            return is_array($value)
                ? array_values(array_filter($value, fn ($item) => ! $matches($item)))
                : ($matches($value) ? null : $value);
        }

        if (is_array($value)) {
            return array_map(fn ($item) => $this->applyOne($item, $name, $argument, $context), $value);
        }

        if ($value === null) {
            return null;
        }

        return match ($name) {
            'trim' => trim((string) $value),
            'collapse_whitespace' => trim(preg_replace('/\s+/u', ' ', (string) $value) ?? ''),
            'lower' => Str::lower((string) $value),
            'upper' => Str::upper((string) $value),
            'strip_tags' => trim(strip_tags((string) $value)),
            // Zero-width space, ZWNJ, ZWJ, word joiner, BOM and soft hyphen.
            // eurogirlsescort.cz sprinkles runs of these through its profile
            // texts, so a scraped "about" ends in a tail of invisible junk
            // that survives trimming and lands in the profile.
            'strip_invisible' => trim(preg_replace(
                '/[\x{200B}\x{200C}\x{200D}\x{2060}\x{FEFF}\x{00AD}]+/u',
                '',
                (string) $value
            ) ?? ''),
            'digits' => preg_replace('/\D+/', '', (string) $value),
            'int' => $this->toInt($value),
            'float' => $this->toFloat($value),
            'boolean' => $this->toBoolean($value),
            'regex' => $this->regex((string) $value, (string) $argument),
            'replace' => $this->replace((string) $value, $argument),
            'prefix' => $argument . $value,
            'suffix' => $value . $argument,
            'absolute_url' => $this->absoluteUrl((string) $value, $context['base_url'] ?? ''),
            'map' => is_array($argument) ? ($argument[(string) $value] ?? $value) : $value,
            default => $value,
        };
    }

    /** First number in the string, so "168 cm" and "cca 168" both work. */
    private function toInt(mixed $value): ?int
    {
        if (preg_match('/-?\d+/', (string) $value, $m)) {
            return (int) $m[0];
        }

        return null;
    }

    private function toFloat(mixed $value): ?float
    {
        $normalized = str_replace(',', '.', (string) $value);

        if (preg_match('/-?\d+(\.\d+)?/', $normalized, $m)) {
            return (float) $m[0];
        }

        return null;
    }

    private function toBoolean(mixed $value): bool
    {
        $normalized = Str::lower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'ano', 'yes', 'y', 'ja'], true);
    }

    /** Returns the first capture group when there is one, else the match. */
    private function regex(string $value, string $pattern): ?string
    {
        if ($pattern === '' || @preg_match($pattern, '') === false) {
            return $value;
        }

        if (preg_match($pattern, $value, $m)) {
            return $m[1] ?? $m[0];
        }

        return null;
    }

    private function replace(string $value, mixed $argument): string
    {
        if (! is_array($argument)) {
            return $value;
        }

        return str_replace($argument[0] ?? '', $argument[1] ?? '', $value);
    }

    private function absoluteUrl(string $value, string $baseUrl): string
    {
        if ($value === '' || $baseUrl === '' || Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        if (Str::startsWith($value, '//')) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https';

            return $scheme . ':' . $value;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($value, '/');
    }
}
