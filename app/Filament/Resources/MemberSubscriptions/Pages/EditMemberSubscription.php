<?php

namespace App\Filament\Resources\MemberSubscriptions\Pages;

use App\Filament\Resources\MemberSubscriptions\MemberSubscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMemberSubscription extends EditRecord
{
    protected static string $resource = MemberSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
