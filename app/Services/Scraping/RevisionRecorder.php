<?php

namespace App\Services\Scraping;

use App\Models\ScrapeItem;
use App\Models\ScrapeItemRevision;
use App\Models\ScrapeRun;

/**
 * Turns „the item changed" into „this is what changed".
 *
 * The run already knew a page had moved — that is how it decided to write at
 * all. What it threw away was the interesting half: which field, from what to
 * what. Recording it costs one row per actual change and nothing at all on the
 * nights when a site sits still.
 */
class RevisionRecorder
{
    /**
     * Compare the stored item against what has just been read, and record the
     * difference. Returns null when nothing moved.
     *
     * @param  array<string, mixed>  $values
     * @param  array<int, string>  $images
     */
    public function record(ScrapeItem $item, ScrapeRun $run, array $values, array $images): ?ScrapeItemRevision
    {
        $changes = $this->diff((array) $item->normalized, $values);

        $before = array_values(array_filter((array) $item->images));
        $after = array_values(array_filter($images));

        $added = array_values(array_diff($after, $before));
        $removed = array_values(array_diff($before, $after));

        if ($changes === [] && $added === [] && $removed === []) {
            return null;
        }

        return ScrapeItemRevision::create([
            'scrape_item_id' => $item->id,
            'scrape_run_id' => $run->id,
            'changes' => $changes === [] ? null : $changes,
            'images_added' => $added === [] ? null : $added,
            'images_removed' => $removed === [] ? null : $removed,
            'is_notable' => $this->isNotable($changes),
        ]);
    }

    /**
     * Field-by-field difference.
     *
     * A field that disappears is a change too — a woman who stopped listing a
     * price is not the same as one who never had one — so both directions are
     * walked.
     *
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return array<string, array{from: mixed, to: mixed}>
     */
    public function diff(array $before, array $after): array
    {
        $changes = [];

        foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $field) {
            $old = $before[$field] ?? null;
            $new = $after[$field] ?? null;

            if ($this->same($old, $new)) {
                continue;
            }

            $changes[$field] = ['from' => $old, 'to' => $new];
        }

        return $changes;
    }

    /**
     * Whether two values are the same as far as a person is concerned.
     *
     * Lists are compared as sets: a site that reorders its list of services
     * between two page loads has not changed anything, and reporting it as a
     * change every night would bury the changes that matter.
     */
    private function same(mixed $old, mixed $new): bool
    {
        if (is_array($old) && is_array($new)) {
            $a = array_map('strval', $old);
            $b = array_map('strval', $new);

            sort($a);
            sort($b);

            return $a === $b;
        }

        // Loose on type, strict on content: „168" from the page and 168 from a
        // transform are the same height.
        if (is_scalar($old) && is_scalar($new)) {
            return (string) $old === (string) $new;
        }

        return $old === $new;
    }

    /** @param array<string, array{from: mixed, to: mixed}> $changes */
    private function isNotable(array $changes): bool
    {
        return array_intersect(array_keys($changes), ScrapeItemRevision::NOTABLE_FIELDS) !== [];
    }
}
