<?php

namespace App\Filament\Resources\Currencies\Pages;

use App\Filament\Resources\Currencies\CurrencyResource;
use App\Models\Currency;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCurrency extends EditRecord
{
    protected static string $resource = CurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Deleting the base currency would leave every other rate without
            // a yardstick.
            DeleteAction::make()->visible(fn (Currency $record) => ! $record->is_base),
        ];
    }
}
