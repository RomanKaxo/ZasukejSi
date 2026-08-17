<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Spatie\Translatable\HasTranslations;
use Throwable;

/**
 * One option in one of the profile's enumerable attribute lists.
 *
 * Eye colour, hair colour and length, bust type and shape, pubic hair and how
 * far somebody travels were scraped and then dropped, because the only list
 * that existed was a hardcoded `['A','B',…]` for bust size inside a Livewire
 * component. With a table behind them the scraper can offer new values for
 * approval the way it does for services.
 */
class ProfileAttributeOption extends Model
{
    use HasFactory;
    use HasTranslations;

    /** Attribute key => label shown in the admin. */
    public const ATTRIBUTES = [
        'bust_size' => 'Velikost prsou',
        'bust_type' => 'Typ prsou',
        'eye_colour' => 'Barva očí',
        'hair_colour' => 'Barva vlasů',
        'hair_length' => 'Délka vlasů',
        'pubic_hair' => 'Ochlupení',
        'travels' => 'Cestování',
        // Jazyky se dosud psaly volným textem, takže „Angličtina", „anglicky"
        // a „EN" byly tři různé hodnoty a nešlo podle nich filtrovat.
        'languages' => 'Jazyky',
    ];

    protected $fillable = [
        'attribute',
        'label',
        'normalized',
        'sort_order',
        'is_active',
    ];

    public array $translatable = ['label'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeForAttribute(Builder $query, string $attribute): Builder
    {
        return $query->where('attribute', $attribute);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected static function booted(): void
    {
        // Kept in step with the label so a renamed option does not stop
        // matching what the scraper offers.
        static::saving(function (self $option) {
            $option->normalized = ScrapeUnknownValue::normalize(
                (string) $option->getTranslation('label', 'cs')
            );
        });
    }

    /**
     * Options of one attribute, in display order.
     *
     * Empty before the table exists, so the app still boots during a migration.
     *
     * @return Collection<int, self>
     */
    public static function listFor(string $attribute): Collection
    {
        try {
            if (! Schema::hasTable('profile_attribute_options')) {
                return collect();
            }

            return static::query()
                ->forAttribute($attribute)
                ->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        } catch (Throwable) {
            return collect();
        }
    }

    /** @return array<string, string> value => label, for a form select */
    public static function optionsFor(string $attribute, ?string $locale = null): array
    {
        return self::listFor($attribute)
            ->mapWithKeys(fn (self $option) => [
                $option->getTranslation('label', 'cs') => $option->getTranslation('label', $locale ?? app()->getLocale()),
            ])
            ->all();
    }

    /**
     * How many profiles currently carry this value.
     *
     * Shown before deactivating one: a value nobody uses can go, a value on
     * forty profiles is a different decision.
     */
    public function profileCount(): int
    {
        try {
            return Profile::query()
                ->where('content->' . $this->attribute, $this->getTranslation('label', 'cs'))
                ->count();
        } catch (Throwable) {
            return 0;
        }
    }

    public static function knows(string $attribute, string $value): bool
    {
        $normalized = ScrapeUnknownValue::normalize($value);

        if ($normalized === '') {
            return true;
        }

        return self::listFor($attribute)->contains(
            fn (self $option) => $option->normalized === $normalized
        );
    }
}
