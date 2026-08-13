<?php

namespace App\Filament\Resources\SubscriptionLogs\Pages;

use App\Filament\Resources\SubscriptionLogs\SubscriptionLogResource;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptionLogs extends ListRecords
{
    protected static string $resource = SubscriptionLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
