<?php

namespace App\Services\Scraping\Catalogues;

use App\Models\ProfileAttributeOption;

/**
 * Which scraped fields our system has a catalogue for.
 *
 * Only fields listed here can produce "unknown value" rows. Free text — a
 * name, a description, an age — has nothing to be unknown against, and
 * putting it in the queue would bury the values that genuinely need a
 * decision.
 */
class CatalogueRegistry
{
    /** @var array<string, FieldCatalogue>|null */
    private ?array $catalogues = null;

    /**
     * Fields that carry a list of values rather than a single one.
     *
     * Services arrive as an array; languages arrive as one string with commas
     * in it („Čeština, Angličtina"), which has to be split or the queue would
     * ask about the whole sentence instead of the two languages in it.
     */
    public const LIST_FIELDS = ['services', 'languages'];

    /** @return array<string, FieldCatalogue> */
    public function all(): array
    {
        if ($this->catalogues !== null) {
            return $this->catalogues;
        }

        $catalogues = [
            'services' => new ServiceCatalogue,
            'city' => new CityCatalogue,
        ];

        foreach (array_keys(ProfileAttributeOption::ATTRIBUTES) as $attribute) {
            $catalogues[$attribute] = new AttributeOptionCatalogue($attribute);
        }

        return $this->catalogues = $catalogues;
    }

    public function for(string $field): ?FieldCatalogue
    {
        return $this->all()[$field] ?? null;
    }

    public function has(string $field): bool
    {
        return $this->for($field) !== null;
    }

    /** @return array<int, string> */
    public function fields(): array
    {
        return array_keys($this->all());
    }

    /** @return array<string, string> field => label, for a filter */
    public function options(): array
    {
        return array_map(fn (FieldCatalogue $catalogue) => $catalogue->label(), $this->all());
    }

    public function isListField(string $field): bool
    {
        return in_array($field, self::LIST_FIELDS, true);
    }

    /**
     * The catalogues are read repeatedly during a harvest and change the
     * moment a value is approved.
     */
    public function forget(): void
    {
        $this->catalogues = null;
    }
}
