<?php

namespace App\Filament\Resources\ScrapeSources\Pages;

use App\Filament\Resources\ScrapeSources\ScrapeSourceResource;
use App\Services\Scraping\SourceConfig;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListScrapeSources extends ListRecords
{
    protected static string $resource = ScrapeSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // The other half of „Stáhnout nastavení". A configuration that
            // took an afternoon to find is otherwise stuck on the machine it
            // was found on.
            Action::make('importConfig')
                ->label('Načíst nastavení ze souboru')
                ->icon('heroicon-o-arrow-up-on-square')
                ->color('gray')
                ->modalHeading('Načíst zdroj z exportu')
                ->modalDescription('Vloží se nastavení i všechna mapování polí. Zdroj se založí vypnutý a bez plánu, ať se dá nejdřív zkontrolovat.')
                ->form([
                    Textarea::make('json')
                        ->label('Obsah souboru')
                        ->rows(12)
                        ->required()
                        ->helperText('Otevřete stažený .json soubor a vložte celý jeho obsah.'),

                    TextInput::make('slug')
                        ->label('Jiný identifikátor')
                        ->helperText('Prázdné = použije se identifikátor ze souboru. Vyplňte, když chcete vedle sebe dvě varianty téhož webu.'),
                ])
                ->action(function (array $data) {
                    try {
                        $source = app(SourceConfig::class)->importJson(
                            (string) $data['json'],
                            $data['slug'] ?: null,
                        );
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Načtení selhalo')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Zdroj načten')
                        ->body(sprintf(
                            '„%s" má %d mapování polí. Je vypnutý — zkontrolujte ho a pak zapněte.',
                            $source->name,
                            $source->fieldMaps()->count(),
                        ))
                        ->success()
                        ->send();
                }),

            CreateAction::make(),
        ];
    }
}
