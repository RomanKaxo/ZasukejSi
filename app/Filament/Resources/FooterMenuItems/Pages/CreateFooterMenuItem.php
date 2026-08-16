<?php

namespace App\Filament\Resources\FooterMenuItems\Pages;

use App\Filament\Resources\FooterMenuItems\FooterMenuItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFooterMenuItem extends CreateRecord
{
    protected static string $resource = FooterMenuItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
