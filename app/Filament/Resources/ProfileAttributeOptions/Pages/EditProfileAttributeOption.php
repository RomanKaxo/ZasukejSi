<?php

namespace App\Filament\Resources\ProfileAttributeOptions\Pages;

use App\Filament\Resources\ProfileAttributeOptions\ProfileAttributeOptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProfileAttributeOption extends EditRecord
{
    protected static string $resource = ProfileAttributeOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Only the locale being edited is touched; the other translations of
        // the label stay as they are.
        if (isset($data['label'])) {
            $translations = $this->record->getTranslations('label');
            $translations[app()->getLocale()] = $data['label'];
            $data['label'] = $translations;
        }

        return $data;
    }
}
