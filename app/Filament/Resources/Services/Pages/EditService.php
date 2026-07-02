<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $currentLocale = app()->getLocale();
        
        // Handle translatable fields - preserve existing translations for other locales
        if (isset($data['name'])) {
            $existingTranslations = $this->record->getTranslations('name');
            $existingTranslations[$currentLocale] = $data['name'];
            $data['name'] = $existingTranslations;
        }

        if (isset($data['description'])) {
            $existingTranslations = $this->record->getTranslations('description');
            $existingTranslations[$currentLocale] = $data['description'];
            $data['description'] = $existingTranslations;
        }

        return $data;
    }
}
