<?php

namespace App\Filament\Resources\ScrapeItems\Pages;

use App\Filament\Resources\ScrapeItems\ScrapeItemResource;
use App\Models\ScrapeItem;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewScrapeItem extends ViewRecord
{
    protected static string $resource = ScrapeItemResource::class;

    public function getTitle(): string
    {
        return (string) ($this->record->value('display_name') ?: 'Položka #' . $this->record->id);
    }

    protected function getHeaderActions(): array
    {
        return [
            // The decision belongs on the screen where the data is, not only in
            // the list where the data is not visible.
            Action::make('approve')
                ->label('Schválit')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn () => $this->record->status === ScrapeItem::STATUS_PENDING)
                ->action(function () {
                    $this->record->update(['status' => ScrapeItem::STATUS_APPROVED]);

                    Notification::make()->title('Schváleno')->success()->send();
                }),

            Action::make('reject')
                ->label('Zamítnout')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Zamítnutou položku další běh scraperu znovu nenabídne.')
                ->visible(fn () => ! in_array($this->record->status, [ScrapeItem::STATUS_IMPORTED, ScrapeItem::STATUS_REJECTED], true))
                ->action(function () {
                    $this->record->update(['status' => ScrapeItem::STATUS_REJECTED]);

                    Notification::make()->title('Zamítnuto')->success()->send();
                }),

            EditAction::make()
                ->label('Upravit údaje')
                ->visible(fn () => $this->record->status !== ScrapeItem::STATUS_IMPORTED),
        ];
    }
}
