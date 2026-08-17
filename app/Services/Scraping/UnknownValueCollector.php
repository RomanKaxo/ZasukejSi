<?php

namespace App\Services\Scraping;

use App\Models\ScrapeItem;
use App\Models\ScrapeUnknownValue;
use App\Models\Service;
use Illuminate\Support\Collection;

/**
 * Works out which of an item's values our catalogue does not know.
 *
 * The importer attaches only services that already exist — a scraped list must
 * not be able to extend our own taxonomy. That rule was silent: a harvest of
 * Brno brought 58 distinct service names, the catalogue knew 10, and the other
 * 48 vanished without anybody being told.
 */
class UnknownValueCollector
{
    /** Memoised for the length of one request; the catalogue is read per item. */
    private ?array $catalogue = null;

    /**
     * Service names on this item that have no catalogue entry.
     *
     * @return Collection<int, string>
     */
    public function unknownServices(ScrapeItem $item): Collection
    {
        $names = collect($item->value('services') ?? [])
            ->map(fn ($name) => trim((string) $name))
            ->filter();

        if ($names->isEmpty()) {
            return collect();
        }

        $known = $this->catalogue();

        return $names
            ->reject(fn (string $name) => in_array(ScrapeUnknownValue::normalize($name), $known, true))
            // Two spellings of one name are one gap, not two.
            ->unique(fn (string $name) => ScrapeUnknownValue::normalize($name))
            ->values();
    }

    /**
     * Whether everything this item mentions is something we can store.
     *
     * An item with nothing unknown can be approved straight away; one with a
     * gap is offered "fill in the missing values" first.
     */
    public function isComplete(ScrapeItem $item): bool
    {
        return $this->unknownServices($item)->isEmpty();
    }

    /**
     * Put an item's gaps into the queue.
     *
     * @return int How many distinct values were noted.
     */
    public function collect(ScrapeItem $item): int
    {
        $unknown = $this->unknownServices($item);

        foreach ($unknown as $name) {
            ScrapeUnknownValue::note(
                ScrapeUnknownValue::FIELD_SERVICES,
                $name,
                $item->scrape_source_id,
            );
        }

        // The flag stays on once set: it is what tells "held back for a value"
        // apart from "nobody ever blocked it" when the value is finally added.
        if ($unknown->isNotEmpty() && $item->unknown_values_at === null) {
            $item->forceFill(['unknown_values_at' => now()])->save();
        }

        return $unknown->count();
    }

    /**
     * Every scrape item that is still waiting on a value nobody has added.
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
     * Items that were held back for a missing value and are not any more.
     *
     * Called after a value is approved into the catalogue: the gap that kept
     * them incomplete may have just been filled.
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

    /** The catalogue changes when a value is approved, so it has to be re-read. */
    public function forget(): void
    {
        $this->catalogue = null;
    }

    /** @return array<int, string> */
    private function catalogue(): array
    {
        return $this->catalogue ??= Service::all()
            ->map(fn (Service $service) => ScrapeUnknownValue::normalize(
                (string) $service->getTranslation('name', 'cs')
            ))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
