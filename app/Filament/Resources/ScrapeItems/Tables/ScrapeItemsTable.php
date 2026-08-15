<?php

namespace App\Filament\Resources\ScrapeItems\Tables;

use App\Models\ScrapeItem;
use App\Services\Scraping\ScrapeItemImporter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class ScrapeItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('normalized.display_name')
                    ->label('Jméno')
                    ->searchable(query: fn ($query, $search) => $query->where('normalized', 'like', "%{$search}%"))
                    ->description(fn (ScrapeItem $record) => $record->value('city'))
                    ->placeholder('—'),

                TextColumn::make('source.name')
                    ->label('Zdroj')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ScrapeItem::statusOptions()[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        ScrapeItem::STATUS_APPROVED => 'success',
                        ScrapeItem::STATUS_IMPORTED => 'info',
                        ScrapeItem::STATUS_REJECTED => 'gray',
                        ScrapeItem::STATUS_FAILED => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('images')
                    ->label('Fotek')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) : 0),

                TextColumn::make('source_url')
                    ->label('Zdrojová adresa')
                    ->limit(40)
                    ->url(fn (ScrapeItem $record) => $record->source_url, true)
                    ->tooltip(fn (ScrapeItem $record) => $record->source_url),

                TextColumn::make('error')
                    ->label('Chyba')
                    ->limit(30)
                    ->color('danger')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Aktualizováno')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Stav')
                    ->options(ScrapeItem::statusOptions()),

                SelectFilter::make('scrape_source_id')
                    ->label('Zdroj')
                    ->relationship('source', 'name'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Schválit')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (ScrapeItem $record) => $record->status === ScrapeItem::STATUS_PENDING)
                    ->action(fn (ScrapeItem $record) => $record->update(['status' => ScrapeItem::STATUS_APPROVED])),

                Action::make('reject')
                    ->label('Zamítnout')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->requiresConfirmation()
                    // Rejected stays rejected: a later run must not put it back
                    // in the queue.
                    ->modalDescription('Zamítnutou položku další běh scraperu znovu nenabídne.')
                    ->visible(fn (ScrapeItem $record) => ! in_array($record->status, [ScrapeItem::STATUS_IMPORTED, ScrapeItem::STATUS_REJECTED], true))
                    ->action(fn (ScrapeItem $record) => $record->update(['status' => ScrapeItem::STATUS_REJECTED])),

                Action::make('import')
                    ->label('Vytvořit profil')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->visible(fn (ScrapeItem $record) => $record->status === ScrapeItem::STATUS_APPROVED)
                    ->form([
                        Toggle::make('with_images')
                            ->label('Stáhnout i fotografie')
                            ->default(true)
                            ->helperText('Stahuje se s prodlevou podle nastavení zdroje, takže to chvíli trvá.'),
                    ])
                    ->requiresConfirmation()
                    ->modalDescription('Vznikne nepublikovaný profil ve stavu ke schválení. Na web se nedostane, dokud ho nepublikujete.')
                    ->action(function (ScrapeItem $record, array $data) {
                        try {
                            $profile = app(ScrapeItemImporter::class)
                                ->import($record, (bool) ($data['with_images'] ?? true));

                            Notification::make()
                                ->title('Profil vytvořen')
                                ->body("#{$profile->id} — nepublikovaný, čeká na schválení.")
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Import selhal')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approveSelected')
                        ->label('Schválit vybrané')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records
                            ->where('status', ScrapeItem::STATUS_PENDING)
                            ->each->update(['status' => ScrapeItem::STATUS_APPROVED])),

                    BulkAction::make('rejectSelected')
                        ->label('Zamítnout vybrané')
                        ->icon('heroicon-o-x-mark')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['status' => ScrapeItem::STATUS_REJECTED])),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
