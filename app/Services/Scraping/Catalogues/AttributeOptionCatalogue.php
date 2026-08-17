<?php

namespace App\Services\Scraping\Catalogues;

use App\Models\ProfileAttributeOption;
use App\Models\ScrapeUnknownValue;
use Illuminate\Database\Eloquent\Model;

/**
 * One of the profile's enumerable attribute lists — eye colour, hair colour
 * and length, bust type and size, pubic hair, travel range.
 */
class AttributeOptionCatalogue implements FieldCatalogue
{
    public function __construct(private readonly string $attribute)
    {
    }

    public function label(): string
    {
        return ProfileAttributeOption::ATTRIBUTES[$this->attribute] ?? $this->attribute;
    }

    public function knows(string $value): bool
    {
        return ProfileAttributeOption::knows($this->attribute, $value);
    }

    public function create(string $value): ?Model
    {
        $normalized = ScrapeUnknownValue::normalize($value);

        if ($normalized === '') {
            return null;
        }

        $existing = ProfileAttributeOption::query()
            ->forAttribute($this->attribute)
            ->where('normalized', $normalized)
            ->first();

        if ($existing) {
            return $existing;
        }

        return ProfileAttributeOption::create([
            'attribute' => $this->attribute,
            'label' => ['cs' => $value, 'en' => $value],
            'sort_order' => (int) ProfileAttributeOption::query()
                ->forAttribute($this->attribute)
                ->max('sort_order') + 1,
            'is_active' => true,
        ]);
    }

    public function canCreate(): bool
    {
        return true;
    }
}
