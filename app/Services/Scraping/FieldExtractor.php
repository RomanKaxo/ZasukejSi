<?php

namespace App\Services\Scraping;

use App\Models\ScrapeFieldMap;
use App\Models\ScrapeSource;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\CssSelector\Exception\ExceptionInterface as CssException;

/**
 * Turns a page plus a set of field maps into a normalized array.
 *
 * CSS selectors are converted to XPath and run through DOMXPath, so no DOM
 * crawler dependency is needed beyond symfony/css-selector, which the project
 * already ships.
 */
class FieldExtractor
{
    private CssSelectorConverter $css;

    private readonly StructuredData $structured;

    public function __construct(
        private readonly Transformers $transformers = new Transformers(),
        ?StructuredData $structured = null,
    ) {
        $this->css = new CssSelectorConverter();
        $this->structured = $structured ?? new StructuredData();
    }

    /**
     * @param  iterable<ScrapeFieldMap>  $fieldMaps
     * @return array{values: array<string, mixed>, missing: array<int, string>}
     */
    public function extract(string $html, iterable $fieldMaps, ScrapeSource $source): array
    {
        $xpath = $this->xpathFor($html);
        $context = ['base_url' => $source->base_url];

        $values = [];
        $missing = [];

        foreach ($fieldMaps as $map) {
            // A `jsonld:` or `meta:` selector reads what the site publishes
            // about itself rather than guessing at its markup.
            $raw = StructuredData::handles($map->selector)
                ? $this->selectStructured($html, $map)
                : $this->select($xpath, $map);

            $value = $this->transformers->apply($raw, $map->transforms ?? [], $context);

            if ($this->isEmpty($value)) {
                if ($map->is_required) {
                    $missing[] = $map->target_field;
                }

                // A field that found nothing is absent, not an empty string —
                // the import step distinguishes the two.
                continue;
            }

            $values[$map->target_field] = $value;
        }

        return ['values' => $values, 'missing' => $missing];
    }

    /**
     * One value from the page's structured data.
     *
     * The `multiple` flag means the same thing here as for a CSS selector, so
     * a site that lists one language and a site that lists five both land in
     * the shape the field expects.
     */
    public function selectStructured(string $html, ScrapeFieldMap $map): mixed
    {
        $value = $this->structured->value($html, $map->selector);

        if ($map->extract === ScrapeFieldMap::EXTRACT_COUNT) {
            return is_array($value) ? count($value) : ($value === null ? 0 : 1);
        }

        if ($map->multiple) {
            return $value === null ? [] : (array) $value;
        }

        return is_array($value) ? ($value[0] ?? null) : $value;
    }

    /** Which structured-data keys a page offers, for the admin to pick from. */
    public function structuredKeys(string $html): array
    {
        return $this->structured->availableKeys($html);
    }

    /** Run one selector and return text, html, an attribute or a count. */
    public function select(DOMXPath $xpath, ScrapeFieldMap $map): mixed
    {
        $nodes = $this->query($xpath, $map->selector);

        if ($nodes === null) {
            return null;
        }

        if ($map->extract === ScrapeFieldMap::EXTRACT_COUNT) {
            return $nodes->length;
        }

        if ($nodes->length === 0) {
            return $map->multiple ? [] : null;
        }

        $read = fn (DOMNode $node) => $this->readNode($node, $map->extract);

        if (! $map->multiple) {
            return $read($nodes->item(0));
        }

        $out = [];
        foreach ($nodes as $node) {
            $value = $read($node);

            if ($value !== null && $value !== '') {
                $out[] = $value;
            }
        }

        return $out;
    }

    /** A selector the admin typed can be invalid; that must not be fatal. */
    private function query(DOMXPath $xpath, string $selector): ?\DOMNodeList
    {
        try {
            $expression = $this->css->toXPath($selector);
        } catch (CssException) {
            // Fall back to treating it as XPath, so both notations work.
            $expression = $selector;
        }

        $nodes = @$xpath->query($expression);

        return $nodes === false ? null : $nodes;
    }

    private function readNode(DOMNode $node, string $extract): ?string
    {
        if (str_starts_with($extract, 'attr:')) {
            $attribute = substr($extract, 5);

            return $node instanceof DOMElement && $node->hasAttribute($attribute)
                ? $node->getAttribute($attribute)
                : null;
        }

        if ($extract === ScrapeFieldMap::EXTRACT_HTML) {
            $html = '';
            foreach ($node->childNodes as $child) {
                $html .= $node->ownerDocument->saveHTML($child);
            }

            return $html;
        }

        return trim(preg_replace('/\s+/u', ' ', $node->textContent) ?? '');
    }

    public function xpathFor(string $html): DOMXPath
    {
        $document = new DOMDocument();

        // Scraped markup is rarely valid; parse errors are expected and muted.
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8" ?>' . $html,
            LIBXML_NOWARNING | LIBXML_NOERROR
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    private function isEmpty(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return is_array($value) && $value === [];
    }
}
