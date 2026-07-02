<?php

namespace App\Filament\Resources\Blogs\Pages;

use App\Filament\Resources\Blogs\BlogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlog extends CreateRecord
{
    protected static string $resource = BlogResource::class;

    /**
     * Mutate the form data before creating the record.
     * Set the type to 'blog' automatically.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'blog';
        $data['display_in_menu'] = false; // Blogs don't show in menu

        return $data;
    }
}
