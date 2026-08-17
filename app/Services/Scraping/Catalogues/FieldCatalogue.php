<?php

namespace App\Services\Scraping\Catalogues;

use Illuminate\Database\Eloquent\Model;

/**
 * What our system already knows for one scraped field, and how to teach it
 * something new.
 *
 * The scraper must never extend a catalogue on its own — that is the rule the
 * whole review queue exists to protect. A catalogue only reports what it
 * knows; creating an entry happens when a person approves it.
 */
interface FieldCatalogue
{
    /** Human label for the queue, e.g. "Služby". */
    public function label(): string;

    /** Whether this value already has a place in our system. */
    public function knows(string $value): bool;

    /**
     * Make it known.
     *
     * Returns null when this catalogue cannot create entries — some fields
     * (an ISO country code, a gender) are fixed sets that an admin resolves by
     * correcting the value, not by adding to them.
     */
    public function create(string $value): ?Model;

    /** Whether `create()` can do anything at all. */
    public function canCreate(): bool;
}
