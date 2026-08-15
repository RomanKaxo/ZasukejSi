<?php

namespace App\Filament\Resources\ScrapeSources\Pages;

use App\Filament\Resources\ScrapeSources\ScrapeSourceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScrapeSources extends ListRecords
{
    protected static string $resource = ScrapeSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
