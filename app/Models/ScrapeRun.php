<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One execution of a source, kept so it is always answerable when a given
 * value was fetched and from where.
 */
class ScrapeRun extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'scrape_source_id',
        'status',
        'started_at',
        'finished_at',
        'pages_fetched',
        'items_found',
        'items_new',
        'items_updated',
        'items_failed',
        'options',
        'error',
        'log',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'options' => 'array',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(ScrapeSource::class, 'scrape_source_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ScrapeItem::class);
    }

    public function durationSeconds(): ?int
    {
        if (! $this->started_at || ! $this->finished_at) {
            return null;
        }

        return $this->finished_at->diffInSeconds($this->started_at);
    }
}
