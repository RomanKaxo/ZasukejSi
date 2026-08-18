<?php

namespace App\Filament\Resources\Profiles\RelationManagers;

use App\Models\ScrapeItem;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Which catalogues this profile is taken from.
 *
 * One woman advertises on three sites. Until items could be attached to an
 * existing profile, that meant three profiles and somebody deleting two by
 * hand; now it means one profile with three sources — and the only place that
 * is visible is here.
 *
 * It also answers the question the removal queue raises: when a source drops
 * her, is she still listed somewhere else?
 */
class ScrapeSourcesRelationManager extends RelationManager
{
    protected static string $relationship = 'scrapeItems';

    protected static ?string $title = 'Zdroje profilu';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source.name')
                    ->label('Web')
                    ->description(fn (ScrapeItem $record) => $record->source?->base_url),

                TextColumn::make('source_url')
                    ->label('Inzerát')
                    ->limit(45)
                    ->url(fn (ScrapeItem $record) => $record->source_url, true)
                    ->tooltip(fn (ScrapeItem $record) => $record->source_url),

                TextColumn::make('imported_at')
                    ->label('Připojeno')
                    ->dateTime('j. n. Y')
                    ->placeholder('—'),

                TextColumn::make('state')
                    ->label('Stav na zdroji')
                    ->state(fn (ScrapeItem $record) => match (true) {
                        $record->missing_resolution !== null => 'vyřešeno',
                        $record->missing_since !== null => 'zmizel ' . $record->missing_since->diffForHumans(),
                        default => 'inzerát je tam',
                    })
                    ->badge()
                    ->color(fn (ScrapeItem $record) => $record->missing_since !== null && $record->missing_resolution === null
                        ? 'danger'
                        : 'success'),

                TextColumn::make('revisions_count')
                    ->label('Změn')
                    ->counts('revisions')
                    ->alignCenter(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Detail položky')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (ScrapeItem $record) => route('filament.admin.resources.scrape-items.view', $record)),

                // Odpojit, když se ukáže, že to přece jen není tatáž osoba.
                // Položka se vrátí ke kontrole, profil zůstává beze změny —
                // jeho hodnoty jsou už dávno jeho vlastní.
                Action::make('detach')
                    ->label('Odpojit')
                    ->icon('heroicon-o-link-slash')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Odpojit zdroj od profilu?')
                    ->modalDescription('Položka se vrátí ke kontrole. Profil ani jeho fotky se nemění — odpojení nic nemaže.')
                    ->action(function (ScrapeItem $record) {
                        $record->forceFill([
                            'imported_profile_id' => null,
                            'imported_at' => null,
                            'status' => ScrapeItem::STATUS_PENDING,
                            'missing_resolution' => null,
                        ])->save();

                        Notification::make()
                            ->title('Zdroj odpojen')
                            ->body('Položka čeká ve frontě ke kontrole.')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([])
            ->toolbarActions([])
            ->defaultSort('imported_at', 'desc')
            ->emptyStateHeading('Profil není ze scraperu')
            ->emptyStateDescription('Vznikl ručně, nebo ho založila sama dívka.');
    }
}
