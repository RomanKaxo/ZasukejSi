<?php

namespace App\Filament\Resources\ScrapeSources\Pages;

use App\Filament\Resources\ScrapeSources\Concerns\HandlesGuidedSettings;
use App\Filament\Resources\ScrapeSources\ScrapeSourceResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditScrapeSource extends EditRecord
{
    use HandlesGuidedSettings;

    protected static string $resource = ScrapeSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resume')
                ->label('Zrušit pauzu')
                ->icon('heroicon-o-play')
                ->color('warning')
                ->visible(fn () => $this->getRecord()->isPaused())
                ->requiresConfirmation()
                ->modalHeading('Vrátit zdroj do plánu?')
                ->modalDescription(fn () => 'Zdroj byl pozastaven: ' . ($this->getRecord()->paused_reason ?: 'bez uvedeného důvodu'))
                ->action(function () {
                    $this->getRecord()->resume();

                    Notification::make()
                        ->title('Zdroj je zase v plánu')
                        ->body('Pokud chyba trvá, pozastaví se znovu.')
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->unpackGuidedSettings($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->packGuidedSettings($data);
    }
}
