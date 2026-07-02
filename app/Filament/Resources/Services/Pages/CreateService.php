<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $currentLocale = app()->getLocale();
        
        // Handle translatable fields - set up initial translations
        if (isset($data['name'])) {
            $data['name'] = [
                $currentLocale => $data['name']
            ];
        }

        if (isset($data['description'])) {
            $data['description'] = [
                $currentLocale => $data['description']
            ];
        }

        return $data;
    }
}
