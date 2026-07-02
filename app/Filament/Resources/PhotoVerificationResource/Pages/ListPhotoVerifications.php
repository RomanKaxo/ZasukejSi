<?php

namespace App\Filament\Resources\PhotoVerificationResource\Pages;

use App\Filament\Resources\PhotoVerificationResource;
use Filament\Resources\Pages\ListRecords;

class ListPhotoVerifications extends ListRecords
{
    protected static string $resource = PhotoVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
