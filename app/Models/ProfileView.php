<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProfileView extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'profile_id',
        'viewer_id',
        'type',
        'ip_address',
        'user_agent',
        'referrer',
        'viewed_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'viewed_date' => 'date',
        ];
    }

    /**
     * View types.
     */
    public const TYPE_CLICK = 'click';
    public const TYPE_IMPRESSION = 'impression';

    /**
     * Get the profile that was viewed.
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the user who viewed the profile.
     */
    public function viewer()
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }

    /**
     * Scope a query to only include clicks.
     */
    public function scopeClicks($query)
    {
        return $query->where('type', self::TYPE_CLICK);
    }

    /**
     * Scope a query to only include impressions.
     */
    public function scopeImpressions($query)
    {
        return $query->where('type', self::TYPE_IMPRESSION);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('viewed_date', [$startDate, $endDate]);
    }

    /**
     * Record a profile click view.
     */
    public static function recordClick(int $profileId, ?int $viewerId = null): self
    {
        return self::create([
            'profile_id' => $profileId,
            'viewer_id' => $viewerId ?? auth()->id(),
            'type' => self::TYPE_CLICK,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->header('referer'),
            'viewed_date' => now()->toDateString(),
        ]);
    }

    /**
     * Record multiple profile impression views (for listing appearances).
     */
    public static function recordImpressions(array $profileIds, ?int $viewerId = null): void
    {
        $viewerId = $viewerId ?? auth()->id();
        $now = now();
        $viewedDate = $now->toDateString();
        $ip = request()->ip();
        $userAgent = request()->userAgent();
        $referrer = request()->header('referer');

        $records = [];
        foreach ($profileIds as $profileId) {
            $records[] = [
                'profile_id' => $profileId,
                'viewer_id' => $viewerId,
                'type' => self::TYPE_IMPRESSION,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'referrer' => $referrer,
                'viewed_date' => $viewedDate,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($records)) {
            self::insert($records);
        }
    }

    /**
     * Get daily stats for a profile within a date range.
     */
    public static function getDailyStats(int $profileId, string $startDate, string $endDate, string $type = null): array
    {
        $query = self::query()
            ->where('profile_id', $profileId)
            ->whereBetween('viewed_date', [$startDate, $endDate])
            ->selectRaw('viewed_date, COUNT(*) as count')
            ->groupBy('viewed_date')
            ->orderBy('viewed_date');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->pluck('count', 'viewed_date')->toArray();
    }

    /**
     * Get total view counts for a profile.
     */
    public static function getTotalStats(int $profileId, string $type = null): int
    {
        $query = self::where('profile_id', $profileId);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->count();
    }
}
