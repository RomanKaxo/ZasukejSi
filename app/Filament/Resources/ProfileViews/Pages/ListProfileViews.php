<?php

namespace App\Filament\Resources\ProfileViews\Pages;

use App\Filament\Resources\ProfileViews\ProfileViewResource;
use Filament\Resources\Pages\ListRecords;

class ListProfileViews extends ListRecords
{
    protected static string $resource = ProfileViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
