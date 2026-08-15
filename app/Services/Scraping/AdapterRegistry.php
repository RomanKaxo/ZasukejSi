<?php

namespace App\Services\Scraping;

use App\Services\Scraping\Adapters\GenericAdapter;
use App\Services\Scraping\Contracts\SourceAdapter;
use InvalidArgumentException;

/**
 * Maps the source's `adapter` column to a class.
 *
 * A site with an unusual listing shape gets a class registered here; anything
 * ordinary stays on `generic` and is configured from settings alone.
 */
class AdapterRegistry
{
    /** @var array<string, class-string<SourceAdapter>> */
    private array $adapters = [
        'generic' => GenericAdapter::class,
    ];

    public function register(string $key, string $class): void
    {
        $this->adapters[$key] = $class;
    }

    /** @return array<string, string> */
    public function options(): array
    {
        return array_combine(
            array_keys($this->adapters),
            array_map(fn ($class) => class_basename($class), $this->adapters),
        );
    }

    public function make(string $key): SourceAdapter
    {
        $class = $this->adapters[$key] ?? null;

        if ($class === null) {
            throw new InvalidArgumentException("Neznámý adaptér [{$key}].");
        }

        return app($class);
    }
}
