<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ScrapeItems\ScrapeItemResource;
use App\Models\ScrapeRun;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * The last few harvests, on the screen the operator lands on.
 *
 * A source whose selectors have gone stale does not announce itself: it keeps
 * running and quietly finds nothing. Seen next to each other, a run that found
 * zero where the previous found forty is obvious.
 */
class RecentScrapeRuns extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function getTableHeading(): string
    {
        return __('filament.dashboard.recent_runs');
    }

    /** Hidden entirely until the scraper has actually been used. */
    public static function canView(): bool
    {
        return ScrapeRun::query()->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ScrapeRun::query()->with('source')->latest('id'))
            ->paginated([5])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('source.name')
                    ->label('Zdroj')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        ScrapeRun::STATUS_COMPLETED => 'success',
                        ScrapeRun::STATUS_FAILED => 'danger',
                        ScrapeRun::STATUS_RUNNING => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('items_found')
                    ->label('Nalezeno')
                    // Zero on a completed run is the shape a broken selector
                    // takes; it deserves to look different from forty.
                    ->color(fn (ScrapeRun $record) => $record->status === ScrapeRun::STATUS_COMPLETED && $record->items_found === 0
                        ? 'danger'
                        : null)
                    ->alignCenter(),

                TextColumn::make('items_new')
                    ->label('Nových')
                    ->color('success')
                    ->alignCenter(),

                TextColumn::make('items_failed')
                    ->label('Chyb')
                    ->color(fn (ScrapeRun $record) => $record->items_failed > 0 ? 'danger' : null)
                    ->alignCenter(),

                TextColumn::make('started_at')
                    ->label('Spuštěno')
                    ->since(),

                TextColumn::make('error')
                    ->label('Chyba')
                    ->limit(40)
                    ->color('danger')
                    ->tooltip(fn (ScrapeRun $record) => $record->error)
                    ->placeholder('—'),
            ])
            ->recordActions([
                Action::make('items')
                    ->label('Položky')
                    ->icon('heroicon-o-inbox-stack')
                    ->url(fn (ScrapeRun $record) => ScrapeItemResource::getUrl('index', [
                        'tableFilters' => [
                            'scrape_run_id' => ['value' => $record->id],
                            'status' => ['value' => null],
                        ],
                    ]))
                    ->visible(fn (ScrapeRun $record) => $record->items()->exists()),
            ]);
    }
}
