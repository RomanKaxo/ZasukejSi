<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Segment extends Model
{
    use HasFactory, HasTranslations;

    /**
     * @var array<int, string>
     */
    public $translatable = ['name'];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'color',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Profiles this segment has been manually assigned to.
     */
    public function profiles()
    {
        return $this->belongsToMany(Profile::class, 'profile_segment')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }
}
