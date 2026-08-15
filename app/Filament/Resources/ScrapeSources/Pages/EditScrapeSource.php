<?php

namespace App\Filament\Resources\ScrapeSources\Pages;

use App\Filament\Resources\ScrapeSources\ScrapeSourceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScrapeSource extends EditRecord
{
    protected static string $resource = ScrapeSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
