<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One scraped entity, staged for review.
 *
 * A row lands here as `pending` and only becomes a Profile when somebody
 * approves it. Nothing published is ever a direct side effect of a run.
 */
class ScrapeItem extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_IMPORTED = 'imported';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'scrape_source_id',
        'scrape_run_id',
        'source_url',
        'external_id',
        'content_hash',
        'raw',
        'normalized',
        'images',
        'status',
        'imported_profile_id',
        'imported_at',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'raw' => 'array',
            'normalized' => 'array',
            'images' => 'array',
            'imported_at' => 'datetime',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Ke kontrole',
            self::STATUS_APPROVED => 'Schváleno',
            self::STATUS_REJECTED => 'Zamítnuto',
            self::STATUS_IMPORTED => 'Importováno',
            self::STATUS_FAILED => 'Chyba',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(ScrapeSource::class, 'scrape_source_id');
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(ScrapeRun::class, 'scrape_run_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'imported_profile_id');
    }

    public function value(string $field, mixed $default = null): mixed
    {
        return $this->normalized[$field] ?? $default;
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
