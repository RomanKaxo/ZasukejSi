<?php

namespace App\Filament\Resources\ScrapeItems\RelationManagers;

use App\Models\ScrapeItemRevision;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * What changed at the source, run by run.
 *
 * A re-scrape overwrote the row and the counter said „aktualizováno" — which
 * field moved, and from what to what, was gone the moment it was written. This
 * is that story: one line per actual change, newest first, and nothing at all
 * on the nights when the site sat still.
 */
class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    protected static ?string $title = 'Co se změnilo na zdroji';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Kdy')
                    ->dateTime('j. n. Y H:i')
                    ->description(fn (ScrapeItemRevision $record) => $record->scrape_run_id
                        ? 'běh #' . $record->scrape_run_id
                        : null)
                    ->sortable(),

                IconColumn::make('is_notable')
                    ->label('Stojí za pozornost')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-minus-small')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(fn (ScrapeItemRevision $record) => $record->is_notable
                        ? 'Změnila se cena, kontakt, jméno, věk nebo město.'
                        : null),

                TextColumn::make('summary')
                    ->label('Souhrn')
                    ->state(fn (ScrapeItemRevision $record) => $record->summary())
                    ->wrap(),

                // Rozepsané „z čeho na co". Kvůli tomu se to celé zaznamenává:
                // souhrn řekne, že se hnula cena, tenhle sloupec o kolik.
                TextColumn::make('detail')
                    ->label('Podrobně')
                    ->state(function (ScrapeItemRevision $record) {
                        $lines = [];

                        foreach ($record->changes ?? [] as $field => $change) {
                            $lines[] = sprintf(
                                '%s: %s → %s',
                                $field,
                                ScrapeItemRevision::readable($change['from'] ?? null),
                                ScrapeItemRevision::readable($change['to'] ?? null),
                            );
                        }

                        foreach ($record->images_added ?? [] as $url) {
                            $lines[] = '+ fotka ' . $url;
                        }

                        foreach ($record->images_removed ?? [] as $url) {
                            $lines[] = '− fotka ' . $url;
                        }

                        return implode("\n", $lines);
                    })
                    ->wrap()
                    ->limit(600),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            // Historie se nezakládá ani neupravuje ručně: je to záznam toho, co
            // se stalo, ne poznámkový blok.
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateHeading('Zatím žádná změna')
            ->emptyStateDescription('Od prvního stažení se na zdroji nic nezměnilo — nebo tu položka je teprve od prvního běhu.');
    }
}
