<?php

namespace App\Filament\Resources\ScrapeUnknownValues\Tables;

use App\Models\ScrapeItem;
use App\Models\ScrapeUnknownValue;
use App\Services\Scraping\UnknownValueCollector;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ScrapeUnknownValuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('value')
                    ->label('Hodnota')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('field')
                    ->label('Pole')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => ScrapeUnknownValue::fieldOptions()[$state] ?? $state),

                // How often a source offered it: a name that turns up on
                // twenty profiles is worth adding, one that turns up once is
                // probably a typo on the source's side.
                TextColumn::make('occurrences')
                    ->label('Výskytů')
                    ->badge()
                    ->color(fn ($state) => (int) $state > 5 ? 'success' : 'gray')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('source.name')
                    ->label('Zdroj')
                    ->badge()
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => ScrapeUnknownValue::statusOptions()[$state] ?? $state)
                    ->color(fn (?string $state) => match ($state) {
                        ScrapeUnknownValue::STATUS_APPROVED => 'success',
                        ScrapeUnknownValue::STATUS_REJECTED => 'gray',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('resolved_at')
                    ->label('Vyřízeno')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('occurrences', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Stav')
                    ->options(fn () => ScrapeUnknownValue::statusOptions())
                    // Opens on what still needs a decision.
                    ->default(ScrapeUnknownValue::STATUS_PENDING),

                SelectFilter::make('field')
                    ->label('Pole')
                    ->options(fn () => ScrapeUnknownValue::fieldOptions()),

                SelectFilter::make('scrape_source_id')
                    ->label('Zdroj')
                    ->relationship('source', 'name'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Doplnit do systému')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->visible(fn (ScrapeUnknownValue $record) => $record->status === ScrapeUnknownValue::STATUS_PENDING)
                    // The admin gets the last word on the wording before it
                    // becomes a permanent catalogue entry.
                    ->form([
                        TextInput::make('name')
                            ->label('Název v našem katalogu')
                            ->helperText('Můžete ho přepsat. Shodný název se nezaloží podruhé.')
                            ->required()
                            ->maxLength(120),
                    ])
                    ->fillForm(fn (ScrapeUnknownValue $record) => ['name' => $record->value])
                    ->action(function (ScrapeUnknownValue $record, array $data) {
                        $service = $record->approve($data['name'] ?? null);

                        if (! $service) {
                            Notification::make()->title('Nepodařilo se doplnit')->danger()->send();

                            return;
                        }

                        $released = self::releaseBlockedItems();

                        Notification::make()
                            ->title('Doplněno do systému')
                            ->body($released > 0
                                ? "Uvolněno položek, které na hodnotu čekaly: {$released}."
                                : 'Žádná položka na tuto hodnotu nečekala.')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Nepřidávat')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('Hodnota se do katalogu nedostane. Položky, které ji zmiňují, zůstanou neúplné.')
                    ->visible(fn (ScrapeUnknownValue $record) => $record->status === ScrapeUnknownValue::STATUS_PENDING)
                    ->action(fn (ScrapeUnknownValue $record) => $record->reject()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approveSelected')
                        ->label('Doplnit vybrané do systému')
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalDescription('Založí se pod názvem tak, jak ho uvedl zdroj. Shodné názvy se nezaloží podruhé.')
                        ->action(function (Collection $records) {
                            $added = 0;

                            foreach ($records->where('status', ScrapeUnknownValue::STATUS_PENDING) as $record) {
                                if ($record->approve()) {
                                    $added++;
                                }
                            }

                            $released = self::releaseBlockedItems();

                            Notification::make()
                                ->title("Doplněno hodnot: {$added}")
                                ->body("Uvolněno položek: {$released}.")
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('rejectSelected')
                        ->label('Nepřidávat vybrané')
                        ->icon('heroicon-o-x-mark')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records
                            ->where('status', ScrapeUnknownValue::STATUS_PENDING)
                            ->each
                            ->reject()),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Nic k doplnění')
            ->emptyStateDescription('Sem se ukládají hodnoty, které zdroj nabídl a náš katalog je nezná — třeba služba, kterou zatím nevedeme.');
    }

    /**
     * Approve every item whose last missing value has just been added.
     *
     * This is the point of the queue: the gap that held an item back is gone,
     * so it should not need a second visit from the reviewer.
     */
    private static function releaseBlockedItems(): int
    {
        $collector = app(UnknownValueCollector::class);
        $importer = app(\App\Services\Scraping\ScrapeItemImporter::class);
        $released = 0;

        foreach ($collector->unblockedItems() as $item) {
            // Only items that were held back for a value are touched; the ones
            // nobody ever blocked are approved by hand as before.
            if (! $item->wasBlockedByUnknownValue()) {
                continue;
            }

            if ($item->status === ScrapeItem::STATUS_PENDING) {
                $item->update(['status' => ScrapeItem::STATUS_APPROVED]);
                $released++;
            }
        }

        // A profile imported while the value was still missing kept the values
        // we could store at the time; now that the gap is filled, it gets the
        // rest without anybody re-importing it.
        foreach (ScrapeItem::query()->where('status', ScrapeItem::STATUS_IMPORTED)->withUnknownValues()->get() as $item) {
            $importer->resyncServices($item);
        }

        return $released;
    }
}
