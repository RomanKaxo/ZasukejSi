<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure proper datetime format
        if (isset($data['starts_at']) && !$data['starts_at'] instanceof \DateTime) {
            $data['starts_at'] = \Carbon\Carbon::parse($data['starts_at']);
        }
        
        if (isset($data['ends_at']) && !$data['ends_at'] instanceof \DateTime) {
            $data['ends_at'] = \Carbon\Carbon::parse($data['ends_at']);
        }

        return $data;
    }
}
