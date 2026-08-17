<?php

namespace App\Models;

use App\Services\Scraping\Catalogues\CatalogueRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A value a source offered that our catalogue does not know.
 *
 * The importer never invents catalogue entries on its own, so these are the
 * ones it had to drop. Approving one creates the real entry; until somebody
 * does, every scrape item that mentions it counts as incomplete.
 */
class ScrapeUnknownValue extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /** The only field we know how to turn into a catalogue entry so far. */
    public const FIELD_SERVICES = 'services';

    protected $fillable = [
        'scrape_source_id',
        'field',
        'value',
        'normalized',
        'occurrences',
        'status',
        'created_type',
        'created_id',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'occurrences' => 'integer',
            'resolved_at' => 'datetime',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(ScrapeSource::class, 'scrape_source_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Diacritics, case and spacing dropped.
     *
     * Two sites will not spell "GFE - společnice" the same way, and we do not
     * want the queue to grow a row per spelling.
     */
    public static function normalize(string $value): string
    {
        $value = Str::of($value)->ascii()->lower()->toString();

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $value) ?? '');
    }

    /**
     * Record that a source offered a value we do not know.
     *
     * Counted rather than duplicated, so the queue shows which unknowns are
     * common enough to be worth adding.
     */
    public static function note(string $field, string $value, ?int $sourceId = null): ?self
    {
        $value = trim($value);
        $normalized = self::normalize($value);

        // Nothing recognisable to add to a catalogue.
        if ($normalized === '') {
            return null;
        }

        $row = static::firstOrNew(['field' => $field, 'normalized' => $normalized]);

        if ($row->exists) {
            // A value already decided on is not re-raised; only the count moves.
            $row->increment('occurrences');

            return $row;
        }

        $row->fill([
            'value' => $value,
            'scrape_source_id' => $sourceId,
            'occurrences' => 1,
            'status' => self::STATUS_PENDING,
        ])->save();

        return $row;
    }

    /**
     * Turn this into a real catalogue entry.
     *
     * Idempotent: an entry that already matches is adopted rather than
     * duplicated, which is what happens when two spellings are approved one
     * after the other.
     */
    public function approve(?string $name = null): ?Model
    {
        $name = trim($name ?: $this->value);

        if ($name === '') {
            return null;
        }

        $catalogue = app(CatalogueRegistry::class)->for($this->field);

        // A field whose set is fixed — an ISO country code, a gender — has
        // nothing to add to; the admin resolves it by correcting the value.
        if (! $catalogue || ! $catalogue->canCreate()) {
            return null;
        }

        $created = $catalogue->create($name);

        if (! $created) {
            return null;
        }

        $this->forceFill([
            'value' => $name,
            'status' => self::STATUS_APPROVED,
            'created_type' => $created::class,
            'created_id' => $created->id,
            'resolved_at' => now(),
        ])->save();

        return $created;
    }

    public function reject(): void
    {
        $this->forceFill([
            'status' => self::STATUS_REJECTED,
            'resolved_at' => now(),
        ])->save();
    }

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Ke schválení',
            self::STATUS_APPROVED => 'Doplněno',
            self::STATUS_REJECTED => 'Zamítnuto',
        ];
    }

    /** @return array<string, string> */
    public static function fieldOptions(): array
    {
        return app(CatalogueRegistry::class)->options();
    }
}
