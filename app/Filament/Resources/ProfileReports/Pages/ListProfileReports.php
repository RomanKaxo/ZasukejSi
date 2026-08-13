<?php

namespace App\Filament\Resources\ProfileReports\Pages;

use App\Filament\Resources\ProfileReports\ProfileReportResource;
use Filament\Resources\Pages\ListRecords;

class ListProfileReports extends ListRecords
{
    protected static string $resource = ProfileReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
