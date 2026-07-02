<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptionTypes extends ListRecords
{
    protected static string $resource = SubscriptionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
