<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One recorded change at the source.
 *
 * Written only when something actually moved, so a nightly run over an
 * unchanged site produces no rows at all — the history is a list of events,
 * not a log of visits.
 */
class ScrapeItemRevision extends Model
{
    protected $fillable = [
        'scrape_item_id',
        'scrape_run_id',
        'changes',
        'images_added',
        'images_removed',
        'is_notable',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'images_added' => 'array',
            'images_removed' => 'array',
            'is_notable' => 'boolean',
        ];
    }

    /**
     * Fields whose change is worth surfacing rather than merely recording.
     *
     * A rewritten „about me" is ordinary; a price that doubled or a phone
     * number that changed is either news or a different woman behind the same
     * advert, and both are worth a person's attention.
     */
    public const NOTABLE_FIELDS = [
        'display_name',
        'phone',
        'whatsapp',
        'telegram',
        'city',
        'country_code',
        'price_hour',
        'price_two_hours',
        'price_night',
        'age',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ScrapeItem::class, 'scrape_item_id');
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(ScrapeRun::class, 'scrape_run_id');
    }

    /** A short human summary: „cena za hodinu, telefon a 2 fotky". */
    public function summary(): string
    {
        $parts = [];

        $fields = array_keys($this->changes ?? []);

        if ($fields !== []) {
            $shown = array_slice($fields, 0, 3);
            $parts[] = implode(', ', $shown) . (count($fields) > 3 ? ' a další' : '');
        }

        $added = count($this->images_added ?? []);
        $removed = count($this->images_removed ?? []);

        if ($added > 0) {
            $parts[] = "+{$added} fotek";
        }

        if ($removed > 0) {
            $parts[] = "−{$removed} fotek";
        }

        return $parts === [] ? 'beze změny' : implode(', ', $parts);
    }

    /** How a value reads in a table cell, whatever shape it arrived in. */
    public static function readable(mixed $value): string
    {
        if ($value === null || $value === '' || $value === []) {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'ano' : 'ne';
        }

        if (is_array($value)) {
            $flat = array_map(
                fn ($item) => is_scalar($item) ? (string) $item : '…',
                array_slice($value, 0, 6),
            );

            return implode(' · ', $flat) . (count($value) > 6 ? ' …' : '');
        }

        $text = (string) $value;

        return mb_strlen($text) > 200 ? mb_substr($text, 0, 200) . '…' : $text;
    }
}
