<?php

namespace App\Filament\Resources\ScrapeItems\Pages;

use App\Filament\Resources\ScrapeItems\ScrapeItemResource;
use Filament\Resources\Pages\EditRecord;

class EditScrapeItem extends EditRecord
{
    protected static string $resource = ScrapeItemResource::class;

    public function getTitle(): string
    {
        return 'Úprava před importem';
    }

    /**
     * Corrections are the reviewer's, not the source's.
     *
     * `raw` keeps what was actually fetched, so a hand-edited value never
     * pretends the site said something it did not.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['raw']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
