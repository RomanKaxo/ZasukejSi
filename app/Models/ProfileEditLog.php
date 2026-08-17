<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * One saved change to a profile.
 *
 * Written by {@see \App\Observers\ProfileEditLogger}. Read-only everywhere
 * else — a log somebody can edit is not a log.
 */
class ProfileEditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['profile_id', 'user_id', 'change_set'];

    protected function casts(): array
    {
        return [
            'change_set' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Field names touched by this change, ready to print. */
    public function fieldList(): string
    {
        return collect(array_keys($this->change_set ?? []))
            ->map(fn (string $field) => self::fieldLabel($field))
            ->implode(', ');
    }

    /** @return array<string, string> */
    public static function fieldLabels(): array
    {
        return [
            'display_name' => 'Jméno',
            'age' => 'Věk',
            'city' => 'Město',
            'country_code' => 'Země',
            'address' => 'Adresa',
            'about' => 'O mně',
            'status' => 'Stav',
            'is_public' => 'Veřejný',
            'phone' => 'Telefon',
            'height_cm' => 'Výška',
            'weight_kg' => 'Váha',
            'bust_size' => 'Prsa',
            'nationality' => 'Národnost',
            'languages' => 'Jazyky',
            'content' => 'Vlastnosti',
            'local_prices' => 'Ceník',
            'global_prices' => 'Ceník (měna)',
            'availability_hours' => 'Dostupnost',
            'verified_at' => 'Ověření',
        ];
    }

    public static function fieldLabel(string $field): string
    {
        return self::fieldLabels()[$field] ?? Str::ucfirst(str_replace('_', ' ', $field));
    }

    /** A value shortened so the log stays readable. */
    public static function short(mixed $value, int $limit = 60): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'ano' : 'ne';
        }

        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return Str::limit((string) $value, $limit);
    }
}
