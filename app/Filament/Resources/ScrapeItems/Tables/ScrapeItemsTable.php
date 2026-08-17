<?php

namespace App\Filament\Resources\ScrapeItems\Tables;

use App\Models\ScrapeItem;
use App\Services\Scraping\DuplicateFinder;
use App\Services\Scraping\ScrapeItemImporter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
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
                // The queue used to be a wall of names. The first photo is what
                // an admin actually recognises a profile by.
                ImageColumn::make('preview')
                    ->label('')
                    ->getStateUsing(fn (ScrapeItem $record) => collect($record->images ?? [])->first())
                    ->height(56)
                    ->extraImgAttributes(['style' => 'object-fit:cover;border-radius:6px;width:42px;height:56px;']),

                TextColumn::make('normalized.display_name')
                    ->label('Jméno')
                    ->searchable(query: fn ($query, $search) => $query->where('normalized', 'like', "%{$search}%"))
                    ->description(fn (ScrapeItem $record) => collect([
                        $record->value('city'),
                        $record->value('age') ? $record->value('age') . ' let' : null,
                    ])->filter()->implode(' · ') ?: null)
                    ->weight('bold')
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
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) : 0)
                    // Nothing to import photos from is worth noticing before
                    // the profile is created, not after.
                    ->color(fn ($state) => is_array($state) && count($state) > 0 ? null : 'warning')
                    ->alignCenter(),

                // The scraper only ever recognised a repeat of itself. The same
                // woman from a second site, or already a profile here, used to
                // sail through and become a duplicate.
                TextColumn::make('duplicate')
                    ->label('Duplicita')
                    ->state(fn (ScrapeItem $record) => $record->duplicateLabel() ?? '—')
                    ->badge()
                    ->color(fn (ScrapeItem $record) => $record->hasDuplicate() ? 'danger' : 'gray')
                    ->url(fn (ScrapeItem $record) => $record->duplicate_profile_id
                        ? route('filament.admin.resources.profiles.view', $record->duplicate_profile_id)
                        : null)
                    ->wrap(),

                TextColumn::make('imported_profile_id')
                    ->label('Profil')
                    ->formatStateUsing(fn ($state) => $state ? '#' . $state : '—')
                    ->url(fn (ScrapeItem $record) => $record->imported_profile_id
                        ? route('filament.admin.resources.profiles.view', $record->imported_profile_id)
                        : null)
                    ->color(fn ($state) => $state ? 'info' : 'gray')
                    ->toggleable(),

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
                    ->options(ScrapeItem::statusOptions())
                    // Opens on the queue: the items waiting for a decision are
                    // the reason to visit this screen.
                    ->default(ScrapeItem::STATUS_PENDING),

                SelectFilter::make('scrape_source_id')
                    ->label('Zdroj')
                    ->relationship('source', 'name'),

                SelectFilter::make('scrape_run_id')
                    ->label('Běh')
                    ->relationship('run', 'id')
                    ->searchable(),

                Filter::make('possible_duplicates')
                    ->label('Možné duplicity')
                    ->query(fn ($query) => $query->possibleDuplicates()),

                // A profile without a photo is worth catching before it exists.
                Filter::make('without_images')
                    ->label('Bez fotografií')
                    ->query(fn ($query) => $query->where(function ($q) {
                        $q->whereNull('images')->orWhere('images', '[]');
                    })),
            ])
            ->recordActions([
                ViewAction::make()->label('Detail'),

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
                    // The warning belongs where the decision is made, not in a
                    // column the reviewer may have scrolled past.
                    ->modalDescription(fn (ScrapeItem $record) => $record->hasDuplicate()
                        ? 'Pozor: vypadá jako už existující — ' . $record->duplicateLabel()
                            . '. Import vytvoří druhý profil. Vznikne nepublikovaný profil ve stavu ke schválení.'
                        : 'Vznikne nepublikovaný profil ve stavu ke schválení. Na web se nedostane, dokud ho nepublikujete.')
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

                    // Importing one at a time made a harvest of fifty profiles
                    // fifty separate confirmations.
                    BulkAction::make('importSelected')
                        ->label('Vytvořit profily z vybraných')
                        ->icon('heroicon-o-user-plus')
                        ->color('primary')
                        ->form([
                            Toggle::make('with_images')
                                ->label('Stáhnout i fotografie')
                                ->default(true)
                                ->helperText('Stahuje se s prodlevou podle nastavení zdroje, takže u většího výběru to trvá.'),
                        ])
                        ->requiresConfirmation()
                        ->modalDescription('Vzniknou nepublikované profily ve stavu ke schválení. Zpracují se jen schválené položky.')
                        ->action(function (Collection $records, array $data) {
                            $importer = app(ScrapeItemImporter::class);
                            $created = 0;
                            $failed = 0;

                            foreach ($records->where('status', ScrapeItem::STATUS_APPROVED) as $record) {
                                try {
                                    $importer->import($record, (bool) ($data['with_images'] ?? true));
                                    $created++;
                                } catch (Throwable $e) {
                                    // One bad row must not stop the rest; the
                                    // reason is kept on the row itself.
                                    $record->forceFill([
                                        'status' => ScrapeItem::STATUS_FAILED,
                                        'error' => $e->getMessage(),
                                    ])->save();
                                    $failed++;
                                }
                            }

                            $skipped = $records->count() - $created - $failed;

                            Notification::make()
                                ->title("Vytvořeno profilů: {$created}")
                                ->body(trim(
                                    ($failed > 0 ? "Selhalo: {$failed}. " : '')
                                    . ($skipped > 0 ? "Přeskočeno neschválených: {$skipped}." : '')
                                ) ?: 'Vše proběhlo bez chyby.')
                                ->color($failed > 0 ? 'warning' : 'success')
                                ->send();
                        }),

                    // The stored verdict is a snapshot from when the item was
                    // staged; a profile created since then would not be in it.
                    BulkAction::make('recheckDuplicates')
                        ->label('Znovu zkontrolovat duplicity')
                        ->icon('heroicon-o-magnifying-glass')
                        ->color('gray')
                        ->action(function (Collection $records) {
                            $finder = app(DuplicateFinder::class);
                            $found = 0;

                            foreach ($records as $record) {
                                $finder->check($record);

                                if ($record->hasDuplicate()) {
                                    $found++;
                                }
                            }

                            Notification::make()
                                ->title("Zkontrolováno: {$records->count()}")
                                ->body($found > 0 ? "Možných duplicit: {$found}." : 'Žádná duplicita nenalezena.')
                                ->color($found > 0 ? 'warning' : 'success')
                                ->send();
                        }),

                    // A failed import is usually a fixable value, not a dead
                    // row — this puts it back in reach instead of leaving it
                    // stuck in an end state.
                    BulkAction::make('retrySelected')
                        ->label('Vrátit ke kontrole')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $reset = $records->whereIn('status', [
                                ScrapeItem::STATUS_FAILED,
                                ScrapeItem::STATUS_REJECTED,
                            ]);

                            foreach ($reset as $record) {
                                $record->forceFill([
                                    'status' => ScrapeItem::STATUS_PENDING,
                                    'error' => null,
                                ])->save();
                            }

                            Notification::make()
                                ->title('Vráceno ke kontrole: ' . $reset->count())
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
