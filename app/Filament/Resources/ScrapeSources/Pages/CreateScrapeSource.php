<?php

namespace App\Filament\Resources\ScrapeSources\Pages;

use App\Filament\Resources\ScrapeSources\Concerns\HandlesGuidedSettings;
use App\Filament\Resources\ScrapeSources\ScrapeSourceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateScrapeSource extends CreateRecord
{
    use HandlesGuidedSettings;

    protected static string $resource = ScrapeSourceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->packGuidedSettings($data);
    }
}
