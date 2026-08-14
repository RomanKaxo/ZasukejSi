<?php

namespace App\Filament\Resources\Translations\Pages;

use App\Filament\Resources\Translations\TranslationResource;
use App\Models\Translation;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTranslations extends ListRecords
{
    protected static string $resource = TranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Pulls in strings added by a deploy without touching anything the
            // operator has already reworded.
            Action::make('import')
                ->label(__('translations.actions.import'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription(__('translations.actions.import_description'))
                ->action(function () {
                    \Illuminate\Support\Facades\Artisan::call('translations:import');

                    Notification::make()
                        ->title(__('translations.actions.import_done'))
                        ->body(trim(\Illuminate\Support\Facades\Artisan::output()))
                        ->success()
                        ->send();
                }),

            Action::make('flush')
                ->label(__('translations.actions.flush'))
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    Translation::flushCache();

                    Notification::make()
                        ->title(__('translations.actions.flush_done'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
