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

    public function __construct(private readonly Transformers $transformers = new Transformers())
    {
        $this->css = new CssSelectorConverter();
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
            $raw = $this->select($xpath, $map);

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
