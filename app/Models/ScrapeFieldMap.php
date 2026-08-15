<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One selector, one target field.
 *
 * This is the row the visual mapping editor writes: pick a value on the page,
 * say which of our fields it is, optionally run it through transforms.
 */
class ScrapeFieldMap extends Model
{
    use HasFactory;

    protected $fillable = [
        'scrape_source_id',
        'target_field',
        'selector',
        'extract',
        'multiple',
        'transforms',
        'is_required',
        'sort_order',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'multiple' => 'boolean',
            'is_required' => 'boolean',
            'transforms' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /** What to take from the matched node. */
    public const EXTRACT_TEXT = 'text';
    public const EXTRACT_HTML = 'html';
    public const EXTRACT_COUNT = 'count';

    public static function extractOptions(): array
    {
        return [
            self::EXTRACT_TEXT => 'Text',
            self::EXTRACT_HTML => 'HTML',
            'attr:href' => 'Atribut href',
            'attr:src' => 'Atribut src',
            'attr:data-src' => 'Atribut data-src',
            'attr:content' => 'Atribut content',
            'attr:title' => 'Atribut title',
            'attr:alt' => 'Atribut alt',
            self::EXTRACT_COUNT => 'Počet nalezených prvků',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(ScrapeSource::class, 'scrape_source_id');
    }
}
