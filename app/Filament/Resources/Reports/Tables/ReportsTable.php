<?php

namespace App\Filament\Resources\Reports\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // One subquery instead of a count per row.
            ->modifyQueryUsing(fn ($query) => $query
                ->with(['profile', 'reporter'])
                ->addSelect(['profile_report_count' => DB::table('reports as sibling')
                    ->selectRaw('count(*)')
                    ->whereColumn('sibling.profile_id', 'reports.profile_id'),
                ]))
            ->columns([
                TextColumn::make('profile.display_name')
                    ->label('Profil')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->profile
                        ? route('filament.admin.resources.profiles.view', $record->profile)
                        : null),

                TextColumn::make('reporter.email')
                    ->label('Nahlásil')
                    ->searchable()
                    ->sortable(),

                // One report is an incident; five against the same profile is
                // a pattern, and that was not visible anywhere.
                TextColumn::make('profile_report_count')
                    ->label('Nahlášení celkem')
                    ->badge()
                    ->color(fn ($state) => (int) $state > 1 ? 'danger' : 'gray')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Důvod')
                    ->limit(60)
                    ->wrap()
                    // Truncated with no way to read the rest.
                    ->tooltip(fn ($record) => $record->reason)
                    ->searchable(),

                TextColumn::make('allegations')
                    ->label('Kategorie')
                    ->badge()
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state),

                IconColumn::make('blocked_at')
                    ->label('Profil zablokován')
                    ->boolean()
                    ->getStateUsing(fn ($record) => filled($record->blocked_at)),

                TextColumn::make('created_at')
                    ->label('Nahlášeno')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('blocked_at')
                    ->label('Stav')
                    ->nullable()
                    ->placeholder('Vše')
                    ->trueLabel('Zablokováno')
                    ->falseLabel('Nevyřešeno')
                    // Opens on what still needs deciding, like the scraper
                    // queue does.
                    ->default(false)
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('blocked_at'),
                        false: fn ($query) => $query->whereNull('blocked_at'),
                    ),
            ])
            ->recordActions([
                Action::make('block')
                    ->label('Zablokovat profil')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => blank($record->blocked_at) && $record->profile)
                    ->action(function ($record) {
                        $record->profile?->update(['is_public' => false]);
                        $record->update(['blocked_at' => now()]);

                        Notification::make()
                            ->title('Profil byl zablokován')
                            ->success()
                            ->send();
                    }),

                Action::make('unblock')
                    ->label('Odblokovat profil')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => filled($record->blocked_at))
                    ->action(function ($record) {
                        $record->profile?->update(['is_public' => true]);
                        $record->update(['blocked_at' => null]);

                        Notification::make()
                            ->title('Profil byl odblokován')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->label('Smazat nahlášení'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
