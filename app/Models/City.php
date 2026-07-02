<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class City extends Model
{
    protected $fillable = [
        'name',
        'name_ascii',
        'country_code',
        'admin_name',
        'lat',
        'lng',
        'population',
    ];

    protected $casts = [
        'lat' => 'decimal:6',
        'lng' => 'decimal:6',
        'population' => 'integer',
    ];

    /**
     * Scope to filter cities by country code (ISO2)
     */
    public function scopeForCountry(Builder $query, string $countryCode): Builder
    {
        return $query->where('country_code', strtoupper($countryCode));
    }

    /**
     * Scope for autocomplete search
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where('name_ascii', 'like', $term . '%')
            ->orWhere('name', 'like', $term . '%');
    }

    /**
     * Get cities for autocomplete based on country
     */
    public static function autocomplete(string $countryCode, string $term, int $limit = 10): \Illuminate\Support\Collection
    {
        return static::forCountry($countryCode)
            ->search($term)
            ->orderByDesc('population')
            ->limit($limit)
            ->pluck('name');
    }
}
