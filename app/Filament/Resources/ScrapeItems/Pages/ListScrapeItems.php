<?php

namespace App\Filament\Resources\ScrapeItems\Pages;

use App\Filament\Resources\ScrapeItems\ScrapeItemResource;
use Filament\Resources\Pages\ListRecords;

/**
 * The review queue opens on the items waiting for a decision. That is done
 * with the status filter's default rather than record tabs, which do not
 * exist as a resource component in this Filament version.
 */
class ListScrapeItems extends ListRecords
{
    protected static string $resource = ScrapeItemResource::class;
}
