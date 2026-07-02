<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    /**
     * Mutate the form data before creating the record.
     * Set the type to 'page' automatically.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'page';

        return $data;
    }
}
