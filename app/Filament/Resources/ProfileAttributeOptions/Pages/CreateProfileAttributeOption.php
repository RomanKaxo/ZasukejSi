<?php

namespace App\Filament\Resources\ProfileAttributeOptions\Pages;

use App\Filament\Resources\ProfileAttributeOptions\ProfileAttributeOptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProfileAttributeOption extends CreateRecord
{
    protected static string $resource = ProfileAttributeOptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['label'])) {
            $data['label'] = [app()->getLocale() => $data['label']];
        }

        return $data;
    }
}
