<?php

namespace App\Services\Scraping;

use App\Models\ScrapeItem;
use App\Models\ScrapeUnknownValue;
use App\Services\Scraping\Catalogues\CatalogueRegistry;
use Illuminate\Support\Collection;

/**
 * Works out which of an item's values our system has nowhere to put.
 *
 * The importer only ever stores what a catalogue already knows — a scraped
 * list must not extend our own taxonomy. The rule is right, but it was
 * silent, and not only for services: eye colour, hair colour and length, bust
 * type, pubic hair and travel range were scraped and dropped without so much
 * as a line in the log.
 */
class UnknownValueCollector
{
    public function __construct(private readonly CatalogueRegistry $catalogues)
    {
    }

    /**
     * Everything on this item that no catalogue knows, per field.
     *
     * @return Collection<string, Collection<int, string>>
     */
    public function unknownValues(ScrapeItem $item): Collection
    {
        $values = (array) ($item->normalized ?? []);
        $gaps = collect();

        foreach ($this->catalogues->all() as $field => $catalogue) {
            if (! array_key_exists($field, $values)) {
                continue;
            }

            $candidates = $this->catalogues->isListField($field)
                // A list field can arrive as an array (services) or as one
                // string with separators in it (languages).
                ? $this->asList($values[$field] ?? null)
                : collect([$values[$field] ?? null]);

            $missing = $candidates
                ->map(fn ($value) => is_scalar($value) ? trim((string) $value) : '')
                ->filter()
                ->reject(fn (string $value) => $catalogue->knows($value))
                // Two spellings of one value are one gap, not two.
                ->unique(fn (string $value) => ScrapeUnknownValue::normalize($value))
                ->values();

            if ($missing->isNotEmpty()) {
                $gaps->put($field, $missing);
            }
        }

        return $gaps;
    }

    /**
     * A list field's values, however the source happened to deliver them.
     *
     * @return Collection<int, mixed>
     */
    private function asList(mixed $value): Collection
    {
        if (is_array($value)) {
            return collect($value);
        }

        if (! is_scalar($value)) {
            return collect();
        }

        return collect(preg_split('/\s*[,;\/|]\s*/u', (string) $value) ?: []);
    }

    /**
     * Service names with no catalogue entry.
     *
     * Kept as its own method because services are the field the review screen
     * talks about most.
     *
     * @return Collection<int, string>
     */
    public function unknownServices(ScrapeItem $item): Collection
    {
        return $this->unknownValues($item)->get('services', collect());
    }

    /** Every gap on the item, flattened for a tooltip or a summary line. */
    public function unknownSummary(ScrapeItem $item): Collection
    {
        return $this->unknownValues($item)->flatMap(
            fn (Collection $values, string $field) => $values->map(
                fn (string $value) => $this->catalogues->for($field)?->label() . ': ' . $value
            )
        )->values();
    }

    /**
     * Whether everything this item mentions is something we can store.
     *
     * A complete item is approved in one click; one with a gap is offered
     * "fill in the missing values" first.
     */
    public function isComplete(ScrapeItem $item): bool
    {
        return $this->unknownValues($item)->isEmpty();
    }

    /**
     * Put an item's gaps into the queue.
     *
     * @return int How many distinct values were noted.
     */
    public function collect(ScrapeItem $item): int
    {
        $gaps = $this->unknownValues($item);
        $noted = 0;

        foreach ($gaps as $field => $values) {
            foreach ($values as $value) {
                ScrapeUnknownValue::note($field, $value, $item->scrape_source_id);
                $noted++;
            }
        }

        // The flag stays on once set: it is what tells "held back for a value"
        // apart from "nobody ever blocked it" when the value is finally added.
        if ($noted > 0 && $item->unknown_values_at === null) {
            $item->forceFill(['unknown_values_at' => now()])->save();
        }

        return $noted;
    }

    /**
     * Every scrape item still waiting on a value nobody has added.
     *
     * @return Collection<int, ScrapeItem>
     */
    public function blockedItems(): Collection
    {
        $this->forget();

        return ScrapeItem::query()
            ->whereIn('status', [ScrapeItem::STATUS_PENDING, ScrapeItem::STATUS_FAILED])
            ->get()
            ->filter(fn (ScrapeItem $item) => ! $this->isComplete($item))
            ->values();
    }

    /**
     * Items held back for a missing value that are not any more.
     *
     * @return Collection<int, ScrapeItem>
     */
    public function unblockedItems(): Collection
    {
        $this->forget();

        return ScrapeItem::query()
            ->where('status', ScrapeItem::STATUS_PENDING)
            ->get()
            ->filter(fn (ScrapeItem $item) => $this->isComplete($item))
            ->values();
    }

    /** The catalogues change when a value is approved, so they are re-read. */
    public function forget(): void
    {
        $this->catalogues->forget();
    }
}
