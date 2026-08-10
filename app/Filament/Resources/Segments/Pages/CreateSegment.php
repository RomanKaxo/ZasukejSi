<?php

namespace App\Filament\Resources\Segments\Pages;

use App\Filament\Resources\Segments\SegmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSegment extends CreateRecord
{
    protected static string $resource = SegmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $currentLocale = app()->getLocale();

        if (isset($data['name'])) {
            $data['name'] = [$currentLocale => $data['name']];
        }

        return $data;
    }
}
