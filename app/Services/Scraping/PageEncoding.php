<?php

namespace App\Services\Scraping;

/**
 * Brings a downloaded page to UTF-8, whatever the site serves it in.
 *
 * Everything downstream — the DOM parser, the transforms, the database —
 * assumes UTF-8, and the parser is even told so outright. A page in
 * windows-1250 therefore did not fail; it succeeded quietly and wrote
 * „Krist��na" into a profile, which is the kind of damage nobody notices
 * until it is on the public site.
 *
 * That encoding is not exotic here. Czech, Slovak and Polish sites built
 * before the late 2000s still serve windows-1250 or iso-8859-2, and Russian
 * ones windows-1251 — exactly the neighbourhood this scraper works in.
 *
 * The declared charset wins over guessing, because a site that says what it
 * sends is usually right and detection on a short page usually is not.
 */
class PageEncoding
{
    /**
     * Aliases seen in the wild that `mb_convert_encoding` does not know, or
     * knows under a different spelling.
     */
    private const ALIASES = [
        'utf8' => 'UTF-8',
        'utf-8' => 'UTF-8',
        'cp1250' => 'Windows-1250',
        'win-1250' => 'Windows-1250',
        'windows1250' => 'Windows-1250',
        'cp1251' => 'Windows-1251',
        'windows1251' => 'Windows-1251',
        'cp1252' => 'Windows-1252',
        'windows1252' => 'Windows-1252',
        'latin2' => 'ISO-8859-2',
        'iso8859-2' => 'ISO-8859-2',
        'iso-8859-2' => 'ISO-8859-2',
        'latin1' => 'ISO-8859-1',
        'iso8859-1' => 'ISO-8859-1',
        'iso-8859-1' => 'ISO-8859-1',
    ];

    /**
     * @param  string|null  $contentType  the response's Content-Type header
     */
    public function toUtf8(string $body, ?string $contentType = null): string
    {
        if ($body === '') {
            return $body;
        }

        $declared = $this->declaredCharset($body, $contentType);
        $charset = $declared ?? $this->guess($body);

        if ($charset === null || $charset === 'UTF-8') {
            // Already UTF-8, or nothing sensible to convert from. Rewriting a
            // declaration we did not act on would be a lie.
            return $charset === 'UTF-8' ? $this->restate($body, $declared) : $body;
        }

        $converted = $this->convert($body, $charset);

        if ($converted === null) {
            return $body;
        }

        // The bytes are UTF-8 now, so a leftover „windows-1250" in the markup
        // would send the DOM parser back the other way.
        return $this->restate($converted, $declared);
    }

    /** The charset the response or the document claims, normalised. */
    public function declaredCharset(string $body, ?string $contentType = null): ?string
    {
        if ($contentType !== null && preg_match('/charset\s*=\s*["\']?([\w-]+)/i', $contentType, $m)) {
            return $this->normalise($m[1]);
        }

        // Sitemaps arrive through the same fetcher, and an XML declaration is
        // the only place they say it.
        if (preg_match('/^\s*<\?xml[^>]+encoding\s*=\s*["\']([\w-]+)/i', $body, $m)) {
            return $this->normalise($m[1]);
        }

        // Only the head is worth reading; a charset declared past the first
        // few kilobytes is ignored by browsers too.
        $head = substr($body, 0, 4096);

        if (preg_match('/<meta[^>]+charset\s*=\s*["\']?([\w-]+)/i', $head, $m)) {
            return $this->normalise($m[1]);
        }

        return null;
    }

    /**
     * Convert to UTF-8, whichever extension knows the encoding.
     *
     * This build's mbstring does not know Windows-1250 — the single most
     * likely encoding for an old Czech, Slovak or Polish page, which is to
     * say the one that matters most here. iconv does, so the two are tried in
     * turn rather than trusting either one.
     */
    private function convert(string $body, string $charset): ?string
    {
        if ($this->mbKnows($charset)) {
            $converted = @mb_convert_encoding($body, 'UTF-8', $charset);

            if (is_string($converted) && $converted !== '') {
                return $converted;
            }
        }

        if (function_exists('iconv')) {
            // //IGNORE: one stray byte in a long page must not cost the page.
            $converted = @iconv($charset, 'UTF-8//IGNORE', $body);

            if (is_string($converted) && $converted !== '') {
                return $converted;
            }
        }

        return null;
    }

    private function mbKnows(string $charset): bool
    {
        return in_array(strtoupper($charset), array_map('strtoupper', mb_list_encodings()), true);
    }

    /** A supported encoding name, or null when neither extension knows it. */
    private function normalise(string $charset): ?string
    {
        $key = strtolower(trim($charset));

        if (isset(self::ALIASES[$key])) {
            return self::ALIASES[$key];
        }

        $name = strtoupper(trim($charset));

        if ($this->mbKnows($name)) {
            return $name;
        }

        // Anything iconv can read is usable even when mbstring cannot.
        return function_exists('iconv') && @iconv($name, 'UTF-8', 'a') !== false
            ? $name
            : null;
    }

    /**
     * What the bytes look like when nobody said.
     *
     * Valid UTF-8 is accepted outright: the sequences are distinctive enough
     * that a false positive on real text is vanishingly unlikely.
     *
     * Beyond that there is no honest detection. Windows-1250, Windows-1251
     * and ISO-8859-2 are all single-byte encodings in which every byte is
     * legal, so „strict" detection just returns whichever is asked about
     * first — it looks like an answer and is a coin toss. Telling them apart
     * needs a model of the language, which is a lot of machinery to guess at
     * something the site could have simply declared.
     *
     * So: an undeclared non-UTF-8 page is treated as Windows-1250. That is
     * what an old Czech, Slovak or Polish site almost always is, and this
     * scraper works in that neighbourhood. A site that means something else
     * only has to say so — a declared charset always wins over this.
     */
    private function guess(string $body): ?string
    {
        return mb_check_encoding($body, 'UTF-8') ? 'UTF-8' : 'Windows-1250';
    }

    /** Make the document say UTF-8, since that is now what it is. */
    private function restate(string $body, ?string $declared): string
    {
        if ($declared === null || $declared === 'UTF-8') {
            return $body;
        }

        $head = substr($body, 0, 4096);
        $rest = substr($body, 4096);

        $head = preg_replace(
            '/(<meta[^>]+charset\s*=\s*["\']?)[\w-]+/i',
            '${1}utf-8',
            $head,
        ) ?? $head;

        $head = preg_replace(
            '/(<\?xml[^>]+encoding\s*=\s*["\'])[\w-]+/i',
            '${1}UTF-8',
            $head,
        ) ?? $head;

        return $head . $rest;
    }
}
