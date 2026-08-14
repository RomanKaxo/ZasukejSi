<?php

namespace App\Filament\Resources\Profiles\Pages;

use App\Filament\Resources\Profiles\ProfileResource;
use App\Support\ProfileContentState;
use Filament\Resources\Pages\CreateRecord;

class CreateProfile extends CreateRecord
{
    protected static string $resource = ProfileResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $currentLocale = app()->getLocale();
        
        // Handle translatable fields - set up initial translations
        if (isset($data['display_name'])) {
            $data['display_name'] = [
                $currentLocale => $data['display_name']
            ];
        }

        if (isset($data['about'])) {
            $data['about'] = [
                $currentLocale => $data['about']
            ];
        }

        // Normalise the attribute map the same way an edit does, so a blank
        // field is stored as absent rather than as an empty string.
        $data['content'] = ProfileContentState::merge(null, $data['content'] ?? []);

        return $data;
    }
}
