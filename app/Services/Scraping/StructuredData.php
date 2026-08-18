<?php

namespace App\Services\Scraping;

use Throwable;

/**
 * What a page says about itself in machine-readable form.
 *
 * Every selector written by hand is a guess about markup somebody else owns,
 * and it breaks the week they redesign. But most modern sites already publish
 * the same facts deliberately — as JSON-LD for search engines, as OpenGraph
 * for the social networks — in a shape that is documented, stable, and the
 * same everywhere. Reading that is not a shortcut; it is the source the site
 * actually maintains.
 *
 * Everything is flattened to dotted keys, so a field map can say
 * `jsonld:name`, `jsonld:address.addressLocality` or `meta:og:title` and never
 * touch the DOM. Where the site publishes nothing, selectors carry on as
 * before — this adds a source, it does not replace one.
 */
class StructuredData
{
    /** Selector prefixes this class answers. */
    public const PREFIX_JSONLD = 'jsonld:';
    public const PREFIX_META = 'meta:';

    /** Keep one page's parse around: a detail page has a dozen field maps. */
    private ?string $memoFor = null;

    /** @var array<string, mixed> */
    private array $memo = [];

    /** Whether a selector asks for structured data rather than the DOM. */
    public static function handles(string $selector): bool
    {
        return str_starts_with($selector, self::PREFIX_JSONLD)
            || str_starts_with($selector, self::PREFIX_META);
    }

    /**
     * One value for a `jsonld:` or `meta:` selector.
     *
     * Returns an array when the page holds several — a gallery, a list of
     * languages — so `multiple` on the field map behaves the same as it does
     * for a CSS selector.
     */
    public function value(string $html, string $selector): mixed
    {
        $data = $this->parse($html);

        $key = str_starts_with($selector, self::PREFIX_JSONLD)
            ? 'jsonld.' . substr($selector, strlen(self::PREFIX_JSONLD))
            : 'meta.' . substr($selector, strlen(self::PREFIX_META));

        return $data[$key] ?? null;
    }

    /**
     * Everything the page publishes, flattened.
     *
     * @return array<string, mixed>
     */
    public function parse(string $html): array
    {
        // Hashing beats keeping the page: a detail page is a few hundred
        // kilobytes and this object outlives the run.
        $fingerprint = sha1($html);

        if ($this->memoFor === $fingerprint) {
            return $this->memo;
        }

        $flat = [];

        foreach ($this->jsonLdBlocks($html) as $block) {
            $this->flatten($block, 'jsonld', $flat);
        }

        foreach ($this->metaTags($html) as $name => $content) {
            $this->push($flat, 'meta.' . $name, $content);
        }

        $this->memoFor = $fingerprint;

        return $this->memo = $flat;
    }

    /**
     * The keys a page offers, for the admin to pick from.
     *
     * @return array<int, string>
     */
    public function availableKeys(string $html): array
    {
        $keys = array_keys($this->parse($html));

        sort($keys);

        return array_map(
            fn (string $key) => str_starts_with($key, 'jsonld.')
                ? self::PREFIX_JSONLD . substr($key, 7)
                : self::PREFIX_META . substr($key, 5),
            $keys,
        );
    }

    /**
     * Decoded `application/ld+json` blocks.
     *
     * A page may carry several, and a valid one may sit next to a broken one,
     * so one bad block must not cost us the rest.
     *
     * @return array<int, array<string, mixed>>
     */
    private function jsonLdBlocks(string $html): array
    {
        if (! preg_match_all(
            '#<script[^>]+type\s*=\s*["\']application/ld\+json["\'][^>]*>(.*?)</script>#is',
            $html,
            $matches,
        )) {
            return [];
        }

        $blocks = [];

        foreach ($matches[1] as $raw) {
            try {
                $decoded = json_decode(trim($raw), true, 64, JSON_THROW_ON_ERROR);
            } catch (Throwable) {
                continue;
            }

            if (! is_array($decoded)) {
                continue;
            }

            // `@graph` is how a site wraps several things in one block; its
            // members are what anybody actually wants.
            if (isset($decoded['@graph']) && is_array($decoded['@graph'])) {
                foreach ($decoded['@graph'] as $node) {
                    if (is_array($node)) {
                        $blocks[] = $node;
                    }
                }

                continue;
            }

            // A bare list of things, rather than one thing.
            if (array_is_list($decoded)) {
                foreach ($decoded as $node) {
                    if (is_array($node)) {
                        $blocks[] = $node;
                    }
                }

                continue;
            }

            $blocks[] = $decoded;
        }

        return $blocks;
    }

    /**
     * `og:`, `twitter:`, `description` and friends.
     *
     * @return array<string, string>
     */
    private function metaTags(string $html): array
    {
        if (! preg_match_all('#<meta\s+([^>]+)>#i', $html, $matches)) {
            return [];
        }

        $tags = [];

        foreach ($matches[1] as $attributes) {
            $name = $this->attribute($attributes, 'property')
                ?? $this->attribute($attributes, 'name')
                ?? $this->attribute($attributes, 'itemprop');

            $content = $this->attribute($attributes, 'content');

            if ($name === null || $content === null || $content === '') {
                continue;
            }

            $tags[strtolower($name)] = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $tags;
    }

    private function attribute(string $attributes, string $name): ?string
    {
        if (preg_match('/\b' . preg_quote($name, '/') . '\s*=\s*"([^"]*)"/i', $attributes, $m)) {
            return $m[1];
        }

        if (preg_match("/\\b" . preg_quote($name, '/') . "\s*=\s*'([^']*)'/i", $attributes, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Turn nested structures into dotted keys.
     *
     * List indices are dropped rather than numbered: `jsonld.image` is what
     * somebody writing a field map thinks of, not `jsonld.image.0`. Repeated
     * keys collect into an array, which is exactly what `multiple` wants.
     *
     * @param  array<string, mixed>  $flat
     */
    private function flatten(mixed $node, string $prefix, array &$flat, int $depth = 0): void
    {
        if ($depth > 8) {
            return;
        }

        if (is_array($node)) {
            foreach ($node as $key => $value) {
                if (is_int($key)) {
                    // A list: every member lands under the same key.
                    $this->flatten($value, $prefix, $flat, $depth + 1);

                    continue;
                }

                // `@type`, `@id` and the like keep their sigil off the key so
                // a field map does not have to escape anything.
                $name = ltrim((string) $key, '@');

                $this->flatten($value, $prefix . '.' . $name, $flat, $depth + 1);
            }

            return;
        }

        if (is_bool($node)) {
            $node = $node ? 'true' : 'false';
        }

        if ($node === null || $node === '') {
            return;
        }

        $this->push($flat, $prefix, (string) $node);
    }

    /** @param array<string, mixed> $flat */
    private function push(array &$flat, string $key, string $value): void
    {
        if (! array_key_exists($key, $flat)) {
            $flat[$key] = $value;

            return;
        }

        if (is_array($flat[$key])) {
            if (! in_array($value, $flat[$key], true)) {
                $flat[$key][] = $value;
            }

            return;
        }

        if ($flat[$key] === $value) {
            return;
        }

        $flat[$key] = [$flat[$key], $value];
    }
}
