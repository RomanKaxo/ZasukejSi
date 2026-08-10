<?php

namespace App\Filament\Resources\Segments\Pages;

use App\Filament\Resources\Segments\SegmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSegment extends EditRecord
{
    protected static string $resource = SegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $currentLocale = app()->getLocale();

        if (isset($data['name'])) {
            $existingTranslations = $this->record->getTranslations('name');
            $existingTranslations[$currentLocale] = $data['name'];
            $data['name'] = $existingTranslations;
        }

        return $data;
    }
}
