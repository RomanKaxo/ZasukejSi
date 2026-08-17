<?php

namespace App\Filament\Resources\ProfileViews\Tables;

use App\Models\Profile;
use App\Models\ProfileView;
use App\Support\ProfileViewSeries;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Návštěvnost po profilech, ne po jednotlivých zobrazeních.
 *
 * Tabulka dřív vypisovala řádek za každou návštěvu. Na rušném webu to jsou
 * desetitisíce řádků, ve kterých se nic nedá najít a hlavně se podle nich
 * nedá seřadit „nejvíc zobrazované dívky" — což je jediná otázka, kterou si
 * provozovatel u téhle sekce klade.
 */
class ProfileViewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $period = self::period();
                $since = ProfileViewSeries::since($period);

                return $query
                    ->with('user')
                    ->withCount([
                        'views as views_total',
                        'views as views_in_period' => fn (Builder $q) => $since
                            ? $q->where('viewed_date', '>=', $since->toDateString())
                            : $q,
                        'views as clicks_in_period' => function (Builder $q) use ($since) {
                            $q->where('type', ProfileView::TYPE_CLICK);

                            if ($since) {
                                $q->where('viewed_date', '>=', $since->toDateString());
                            }
                        },
                    ]);
            })
            ->columns([
                TextColumn::make('display_name')
                    ->label('Profil')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Profile $record) => $record->user?->email ?? 'bez uživatele')
                    ->url(fn (Profile $record) => route('filament.admin.resources.profiles.view', $record)),

                TextColumn::make('views_in_period')
                    ->label(fn () => ProfileViewSeries::periods()[self::period()])
                    ->badge()
                    ->color('success')
                    ->alignCenter()
                    // Tohle je ten sloupec, kvůli kterému sem člověk chodí.
                    ->sortable(),

                TextColumn::make('views_total')
                    ->label('Celkem')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('clicks_in_period')
                    ->label('Z toho kliků')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),

                ViewColumn::make('trend')
                    ->label(fn () => ProfileViewSeries::isDaily(self::period()) ? 'Průběh po dnech' : 'Průběh po měsících')
                    ->view('filament.columns.view-sparkline')
                    ->state(function (Profile $record) {
                        $period = self::period();

                        // Prázdný seznam = všechny profily jedním dotazem.
                        // Seznam řádků stránky tu k dispozici není, a dotaz
                        // na každý řádek zvlášť by byl dvacet pět dotazů.
                        $buckets = app(ProfileViewSeries::class)->buckets([], $period);

                        return ProfileViewSeries::seriesFor($buckets, $record->id, $period);
                    }),
            ])
            ->defaultSort('views_in_period', 'desc')
            ->filters([
                SelectFilter::make('period')
                    ->label('Období')
                    ->options(ProfileViewSeries::periods())
                    ->default(ProfileViewSeries::DEFAULT_PERIOD)
                    // Období řídí, co se počítá a co kreslí graf; samo o sobě
                    // řádky nefiltruje.
                    ->query(fn (Builder $query) => $query),

                SelectFilter::make('status')
                    ->label('Stav profilu')
                    ->options([
                        'approved' => 'Schválený',
                        'pending' => 'Čeká',
                        'rejected' => 'Zamítnutý',
                    ]),
            ])
            ->recordActions([
                Action::make('detail')
                    ->label('Statistiky profilu')
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn (Profile $record) => route('filament.admin.resources.profiles.view', $record)),
            ])
            ->emptyStateHeading('Zatím žádná zobrazení')
            ->emptyStateDescription('Jakmile někdo otevře profil na webu, objeví se tu.');
    }

    /** Vybrané období z filtru, nebo výchozí. */
    private static function period(): string
    {
        $filters = request()->input('tableFilters.period.value');

        return ProfileViewSeries::normalisePeriod(is_string($filters) ? $filters : null);
    }
}
