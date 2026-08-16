<?php

namespace App\Filament\Resources\FooterMenuItems\Pages;

use App\Filament\Resources\FooterMenuItems\FooterMenuItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFooterMenuItems extends ListRecords
{
    protected static string $resource = FooterMenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Přidat odkaz'),
        ];
    }
}
