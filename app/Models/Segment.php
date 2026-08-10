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
     * spatie/laravel-translatable only falls back to a single configured
     * locale (`config('app.fallback_locale')`), which in this app is the
     * same as `config('app.locale')` ("en"). That means a segment whose
     * name has only been translated into a non-default locale (e.g. only
     * "cs" has been filled in so far) would resolve to an empty string for
     * every other locale, since "en" is never among its translations.
     * Overriding this method (an extension point read by
     * HasTranslations::normalizeLocale()) makes the fallback resolve to
     * whichever locale the name actually has a translation for, so admins
     * always see *something* rather than a blank segment name.
     */
    public function getFallbackLocale(): ?string
    {
        $translatedLocales = $this->getTranslatedLocales('name');

        return $translatedLocales[0] ?? config('app.fallback_locale');
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
