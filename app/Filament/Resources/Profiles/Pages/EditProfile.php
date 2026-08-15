<?php

namespace App\Filament\Resources\Profiles\Pages;

use App\Filament\Resources\Profiles\Concerns\SyncsServicePrices;
use App\Filament\Resources\Profiles\ProfileResource;
use App\Support\ProfileContentState;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProfile extends EditRecord
{
    use SyncsServicePrices;

    protected static string $resource = ProfileResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['servicePrices'] = $this->readServicePrices($this->record);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->writeServicePrices($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $currentLocale = app()->getLocale();
        
        // Handle translatable fields - preserve existing translations for other locales
        if (isset($data['display_name'])) {
            $existingTranslations = $this->record->getTranslations('display_name');
            $existingTranslations[$currentLocale] = $data['display_name'];
            $data['display_name'] = $existingTranslations;
        }

        if (isset($data['about'])) {
            $existingTranslations = $this->record->getTranslations('about');
            $existingTranslations[$currentLocale] = $data['about'];
            $data['about'] = $existingTranslations;
        }

        $data['content'] = ProfileContentState::merge($this->record->content, $data['content'] ?? []);

        // Not a column; held aside and written to the pivot in afterSave().
        return $this->extractServicePrices($data);
    }
}
