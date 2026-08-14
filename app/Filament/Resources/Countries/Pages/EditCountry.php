<?php

namespace App\Filament\Resources\Countries\Pages;

use App\Filament\Resources\Countries\CountryResource;
use App\Services\CountryStatsService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCountry extends EditRecord
{
    protected static string $resource = CountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Merge the edited locale into the existing translations rather than
     * replacing them, so editing the Czech name does not wipe the English one.
     * Clearing the field removes the override entirely and the country falls
     * back to its standard ISO name.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $translations = $this->record->getTranslations('name_override');
        $locale = app()->getLocale();

        if (filled($data['name_override'] ?? null)) {
            $translations[$locale] = $data['name_override'];
        } else {
            unset($translations[$locale]);
        }

        $data['name_override'] = $translations === [] ? null : $translations;

        return $data;
    }

    protected function afterSave(): void
    {
        CountryStatsService::flush();
    }
}
