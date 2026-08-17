<?php

namespace App\Filament\Resources\ProfileAttributeOptions\Pages;

use App\Filament\Resources\ProfileAttributeOptions\ProfileAttributeOptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProfileAttributeOptions extends ListRecords
{
    protected static string $resource = ProfileAttributeOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
