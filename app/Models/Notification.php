<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'audience',
        'is_global',
        'read_at',
        'archived_at',
    ];

    protected $casts = [
        'is_global' => 'boolean',
        'read_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    /** Komu je zpráva určená. */
    public const AUDIENCE_PUBLIC = 'public';
    public const AUDIENCE_ADMIN = 'admin';

    /**
     * A notice for whoever runs the site, not for its visitors.
     *
     * Provozní věci — scraper narazil na blokaci, profily zmizely ze zdroje —
     * chodily jako globální notifikace, tedy každé dívce a každému členovi.
     * O údržbě, se kterou nemůžou nic dělat.
     */
    public static function forAdmins(string $title, string $message, string $type = 'warning'): self
    {
        return self::create([
            'user_id' => null,
            'is_global' => false,
            'audience' => self::AUDIENCE_ADMIN,
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ]);
    }

    /** Zprávy pro administraci, od nejnovější. */
    public function scopeForAdmins($query)
    {
        return $query->where('audience', self::AUDIENCE_ADMIN);
    }

    /** Všechno, co smí vidět návštěvník. */
    public function scopePublicAudience($query)
    {
        return $query->where('audience', '!=', self::AUDIENCE_ADMIN);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Per-user read/archived state, used only for global notifications.
     */
    public function userStates()
    {
        return $this->hasMany(NotificationUserState::class);
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function archive()
    {
        $this->update(['archived_at' => now()]);
    }

    /**
     * Mark a GLOBAL notification as read for one user only.
     */
    public function markAsReadForUser($userId): void
    {
        NotificationUserState::updateOrCreate(
            ['notification_id' => $this->id, 'user_id' => $userId],
            ['read_at' => now()]
        );
    }

    /**
     * Archive a GLOBAL notification for one user only, leaving the shared row
     * (and therefore every other user's copy) untouched.
     */
    public function archiveForUser($userId): void
    {
        NotificationUserState::updateOrCreate(
            ['notification_id' => $this->id, 'user_id' => $userId],
            ['archived_at' => now(), 'read_at' => now()]
        );
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeForUser($query, $userId)
    {
        // Provozní zprávy se do uživatelského zvonku nedostanou, ať je
        // založí kdokoli — filtr je tady, ne na volajícím místě.
        return $query
            ->publicAudience()
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('is_global', true);
            });
    }

    /**
     * Notifications currently visible in a user's feed: their own un-archived
     * ones plus global ones they have not archived for themselves.
     */
    public function scopeActiveForUser($query, $userId)
    {
        return $query->forUser($userId)
            ->whereNull('archived_at')
            ->whereDoesntHave('userStates', function ($q) use ($userId) {
                $q->where('user_id', $userId)->whereNotNull('archived_at');
            });
    }

    /**
     * Notifications a user has archived: their own archived ones plus global
     * ones they archived for themselves.
     */
    public function scopeArchivedForUser($query, $userId)
    {
        return $query->forUser($userId)
            ->where(function ($q) use ($userId) {
                $q->whereNotNull('archived_at')
                  ->orWhereHas('userStates', function ($inner) use ($userId) {
                      $inner->where('user_id', $userId)->whereNotNull('archived_at');
                  });
            });
    }

    /**
     * Whether this notification counts as read for the given user.
     */
    public function isReadBy($userId): bool
    {
        if (! $this->is_global) {
            return $this->read_at !== null;
        }

        return $this->userStates
            ->where('user_id', $userId)
            ->whereNotNull('read_at')
            ->isNotEmpty();
    }

    /**
     * The types a notification can carry, and how each one is coloured.
     *
     * Kept here rather than in the dropdown blade so the bell, the admin table
     * and anything added later cannot drift into showing the same type in
     * different colours.
     *
     * @return array<string, array{label: string, hex: string, filament: string}>
     */
    public static function types(): array
    {
        return [
            'success' => ['label' => __('notifications.types.success'), 'hex' => '#00B80F', 'filament' => 'success'],
            'info' => ['label' => __('notifications.types.info'), 'hex' => '#2490FF', 'filament' => 'info'],
            'warning' => ['label' => __('notifications.types.warning'), 'hex' => '#FFB700', 'filament' => 'warning'],
            'danger' => ['label' => __('notifications.types.danger'), 'hex' => '#DD3888', 'filament' => 'danger'],
            'system' => ['label' => __('notifications.types.system'), 'hex' => '#5C2D62', 'filament' => 'gray'],
        ];
    }

    /** @return array<string, string> */
    public static function typeOptions(): array
    {
        return collect(self::types())->map(fn ($meta) => $meta['label'])->all();
    }

    /** Accent colour for the frontend bell. */
    public function typeColor(): string
    {
        return self::types()[$this->type]['hex'] ?? self::types()['system']['hex'];
    }

    /** Badge colour for the admin table. */
    public function typeBadgeColor(): string
    {
        return self::types()[$this->type]['filament'] ?? 'gray';
    }

    public function typeLabel(): string
    {
        return self::types()[$this->type]['label'] ?? (string) $this->type;
    }

    public static function createGlobal($title, $message, $type = 'info')
    {
        return static::create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_global' => true,
        ]);
    }

    /**
     * A notice whose text is shown in the reader's language.
     *
     * The text used to be rendered when the event happened, i.e. in the
     * language of whoever triggered it: an admin approving a profile in the
     * English admin sent the Czech provider "Profile Approved". The keys are
     * stored instead ("notifications.x::{params}") and translated on display.
     */
    public static function createTranslatedForUser($userId, string $titleKey, string $messageKey, array $params = [], string $type = 'info')
    {
        return static::createForUser(
            $userId,
            static::encodeTranslationKey($titleKey, $params),
            static::encodeTranslationKey($messageKey, $params),
            $type
        );
    }

    protected static function encodeTranslationKey(string $key, array $params): string
    {
        return $params === [] ? $key : $key . '::' . json_encode($params, JSON_UNESCAPED_UNICODE);
    }

    /** Translate a stored key (see createTranslatedForUser); plain text passes through. */
    public static function translateStored(?string $value): ?string
    {
        if ($value === null || ! str_starts_with($value, 'notifications.')) {
            return $value;
        }

        if (! preg_match('/^(notifications\.[A-Za-z0-9_.]+)(?:::(\{.*\}))?$/s', $value, $m) || ! \Illuminate\Support\Facades\Lang::has($m[1])) {
            return $value;
        }

        $params = isset($m[2]) ? (json_decode($m[2], true) ?: []) : [];

        return __($m[1], $params);
    }

    public function getTitleAttribute($value): ?string
    {
        return static::translateStored($value);
    }

    public function getMessageAttribute($value): ?string
    {
        return static::translateStored($value);
    }

    public static function createForUser($userId, $title, $message, $type = 'info')
    {
        return static::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_global' => false,
        ]);
    }
}
