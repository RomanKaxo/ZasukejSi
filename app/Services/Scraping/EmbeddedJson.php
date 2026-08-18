<?php

namespace App\Services\Scraping;

use Throwable;

/**
 * The data a JavaScript site ships before it renders anything.
 *
 * A page built by React or Vue arrives as an empty shell: the selectors find
 * nothing, the run reports „nalezeno 0", and the honest answer used to be „the
 * scraper cannot read this site". Running a browser to fix that means shipping
 * Chrome, a queue and a lot of memory for a handful of sites.
 *
 * But the shell is not actually empty. To avoid fetching the same data twice,
 * these frameworks embed it in the page — `__NEXT_DATA__`, `__NUXT__`,
 * `application/json` blocks — and that copy is better than anything a selector
 * could scrape: it is the site's own structure, with real types, and it does
 * not move when the design does.
 *
 * A field map reaches it as `json:props.pageProps.profile.name`. The path is
 * dotted, list indices are dropped the same way as in structured data, so
 * repeated keys collect into an array.
 *
 * This does not make the scraper a browser. A site that fetches its content
 * over XHR after load leaves nothing in the HTML and still cannot be read
 * without one — the workbench says so rather than pretending otherwise.
 */
class EmbeddedJson
{
    public const PREFIX = 'json:';

    /** How deep to walk before deciding the structure is pathological. */
    private const MAX_DEPTH = 12;

    private ?string $memoFor = null;

    /** @var array<string, mixed> */
    private array $memo = [];

    public static function handles(string $selector): bool
    {
        return str_starts_with($selector, self::PREFIX);
    }

    public function value(string $html, string $selector): mixed
    {
        $key = substr($selector, strlen(self::PREFIX));

        return $this->parse($html)[$key] ?? null;
    }

    /**
     * Everything the page embeds, flattened to dotted keys.
     *
     * @return array<string, mixed>
     */
    public function parse(string $html): array
    {
        $fingerprint = sha1($html);

        if ($this->memoFor === $fingerprint) {
            return $this->memo;
        }

        $flat = [];

        foreach ($this->blocks($html) as $block) {
            $this->flatten($block, '', $flat);
        }

        $this->memoFor = $fingerprint;

        return $this->memo = $flat;
    }

    /** @return array<int, string> */
    public function availableKeys(string $html): array
    {
        $keys = array_keys($this->parse($html));

        sort($keys);

        return array_map(fn (string $key) => self::PREFIX . $key, $keys);
    }

    /**
     * Whether this page looks like it renders in the browser.
     *
     * Not a certainty and not treated as one: it is what the workbench uses to
     * say „this is why your selectors find nothing" instead of leaving somebody
     * to work it out.
     */
    public function looksClientRendered(string $html): bool
    {
        // Text a person would see, minus scripts and styles.
        $visible = trim(strip_tags(preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', ' ', $html) ?? ''));

        $scripts = preg_match_all('#<script\b#i', $html);

        // Hardly any words, and scripts doing the work. Deliberately without a
        // minimum page size: a small page with two scripts and no text is
        // unreadable to a selector too, so saying so is correct rather than
        // over-eager.
        return mb_strlen($visible) < 400 && $scripts >= 2;
    }

    /**
     * Decoded JSON blocks embedded in the page.
     *
     * @return array<int, array<string, mixed>>
     */
    private function blocks(string $html): array
    {
        $blocks = [];

        // <script type="application/json" id="__NEXT_DATA__">…</script>
        if (preg_match_all(
            '#<script[^>]+type\s*=\s*["\']application/json["\'][^>]*>(.*?)</script>#is',
            $html,
            $matches,
        )) {
            foreach ($matches[1] as $raw) {
                $decoded = $this->decode($raw);

                if ($decoded !== null) {
                    $blocks[] = $decoded;
                }
            }
        }

        // window.__NUXT__ = {...}; a jeho příbuzní.
        if (preg_match_all(
            '#window\.(__NUXT__|__INITIAL_STATE__|__APOLLO_STATE__|__DATA__)\s*=\s*(\{.*?\})\s*[;<]#s',
            $html,
            $matches,
            PREG_SET_ORDER,
        )) {
            foreach ($matches as $match) {
                $decoded = $this->decode($match[2]);

                if ($decoded !== null) {
                    $blocks[] = $decoded;
                }
            }
        }

        return $blocks;
    }

    /** @return array<string, mixed>|null */
    private function decode(string $raw): ?array
    {
        $raw = trim($raw);

        if ($raw === '' || strlen($raw) > 4_000_000) {
            return null;
        }

        try {
            $decoded = json_decode($raw, true, 128, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    /** @param array<string, mixed> $flat */
    private function flatten(mixed $node, string $prefix, array &$flat, int $depth = 0): void
    {
        if ($depth > self::MAX_DEPTH || count($flat) > 5000) {
            return;
        }

        if (is_array($node)) {
            foreach ($node as $key => $value) {
                if (is_int($key)) {
                    $this->flatten($value, $prefix, $flat, $depth + 1);

                    continue;
                }

                $name = ltrim((string) $key, '@');

                $this->flatten($value, $prefix === '' ? $name : $prefix . '.' . $name, $flat, $depth + 1);
            }

            return;
        }

        if (is_bool($node)) {
            $node = $node ? 'true' : 'false';
        }

        if ($node === null || $node === '' || $prefix === '') {
            return;
        }

        $value = (string) $node;

        if (! array_key_exists($prefix, $flat)) {
            $flat[$prefix] = $value;

            return;
        }

        if (is_array($flat[$prefix])) {
            if (! in_array($value, $flat[$prefix], true)) {
                $flat[$prefix][] = $value;
            }

            return;
        }

        if ($flat[$prefix] !== $value) {
            $flat[$prefix] = [$flat[$prefix], $value];
        }
    }
}
