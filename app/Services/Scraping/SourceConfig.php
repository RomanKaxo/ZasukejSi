<?php

namespace App\Services\Scraping;

use App\Models\ScrapeFieldMap;
use App\Models\ScrapeSource;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * A whole source — settings and every selector — as one portable file.
 *
 * Setting a site up is the expensive part of scraping: half a day of finding
 * selectors that nobody wants to repeat on the staging server, and then again
 * after a restore. Worse, it was work that could not be handed over: the only
 * way to give somebody a working configuration was a screenshot of the form.
 *
 * Nothing scraped travels with it — no items, no runs, no photographs. This is
 * the recipe, not the harvest.
 */
class SourceConfig
{
    /** Bumped when the shape changes, so an old file fails loudly. */
    public const VERSION = 1;

    /** @return array<string, mixed> */
    public function export(ScrapeSource $source): array
    {
        return [
            'version' => self::VERSION,
            'exported_at' => now()->toIso8601String(),
            'source' => [
                'name' => $source->name,
                'slug' => $source->slug,
                'base_url' => $source->base_url,
                'adapter' => $source->adapter,
                'schedule_hours' => $source->schedule_hours,
                'schedule_pages' => $source->schedule_pages,
                'schedule_limit' => $source->schedule_limit,
                'settings' => $source->settings ?? [],
                'notes' => $source->notes,
            ],
            'field_maps' => $source->fieldMaps()
                ->orderBy('sort_order')
                ->get()
                ->map(fn (ScrapeFieldMap $map) => [
                    'target_field' => $map->target_field,
                    'selector' => $map->selector,
                    'extract' => $map->extract,
                    'multiple' => (bool) $map->multiple,
                    'transforms' => $map->transforms ?? [],
                    'is_required' => (bool) $map->is_required,
                    'sort_order' => (int) $map->sort_order,
                    'note' => $map->note,
                ])
                ->all(),
        ];
    }

    public function toJson(ScrapeSource $source): string
    {
        return json_encode($this->export($source), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /** A filename that says which site and when. */
    public function filename(ScrapeSource $source): string
    {
        return 'zdroj-' . $source->slug . '-' . now()->format('Y-m-d') . '.json';
    }

    /**
     * Create or update a source from an exported file.
     *
     * Deliberately never enables the source and never sets a schedule slot: an
     * imported configuration is a draft until somebody looks at it. It is also
     * imported switched off, so a wrong selector cannot start hammering a site
     * the moment the file lands.
     *
     * @param  array<string, mixed>  $payload
     */
    public function import(array $payload, ?string $slugOverride = null): ScrapeSource
    {
        $version = $payload['version'] ?? null;

        if ($version !== self::VERSION) {
            throw new InvalidArgumentException('Soubor je z jiné verze (' . var_export($version, true) . '), čekáme ' . self::VERSION . '.');
        }

        $data = $payload['source'] ?? null;

        if (! is_array($data) || ! isset($data['base_url'])) {
            throw new InvalidArgumentException('V souboru chybí popis zdroje.');
        }

        $slug = $slugOverride ?: (string) ($data['slug'] ?? Str::slug((string) ($data['name'] ?? 'zdroj')));
        $slug = Str::slug($slug) ?: 'zdroj';

        $source = ScrapeSource::firstOrNew(['slug' => $slug]);

        $source->fill([
            'name' => (string) ($data['name'] ?? $slug),
            'base_url' => rtrim((string) $data['base_url'], '/'),
            'adapter' => (string) ($data['adapter'] ?? 'generic'),
            'schedule_hours' => $data['schedule_hours'] ?? null,
            'schedule_pages' => $data['schedule_pages'] ?? null,
            'schedule_limit' => $data['schedule_limit'] ?? null,
            'settings' => is_array($data['settings'] ?? null) ? $data['settings'] : [],
            'notes' => $data['notes'] ?? null,
        ]);

        $source->slug = $slug;
        $source->is_enabled = false;
        $source->next_run_at = null;
        $source->save();

        // Selectors are replaced wholesale rather than merged: a half-imported
        // set of field maps is worse than either version on its own.
        $source->fieldMaps()->delete();

        foreach (($payload['field_maps'] ?? []) as $index => $map) {
            if (! is_array($map) || ! isset($map['target_field'], $map['selector'])) {
                continue;
            }

            $source->fieldMaps()->create([
                'target_field' => (string) $map['target_field'],
                'selector' => (string) $map['selector'],
                'extract' => (string) ($map['extract'] ?? ScrapeFieldMap::EXTRACT_TEXT),
                'multiple' => (bool) ($map['multiple'] ?? false),
                'transforms' => is_array($map['transforms'] ?? null) ? $map['transforms'] : [],
                'is_required' => (bool) ($map['is_required'] ?? false),
                'sort_order' => (int) ($map['sort_order'] ?? $index),
                'note' => $map['note'] ?? null,
            ]);
        }

        return $source->refresh();
    }

    /** Import straight from the text of a file. */
    public function importJson(string $json, ?string $slugOverride = null): ScrapeSource
    {
        $payload = json_decode(trim($json), true);

        if (! is_array($payload)) {
            throw new InvalidArgumentException('Tohle není platný JSON.');
        }

        return $this->import($payload, $slugOverride);
    }
}
