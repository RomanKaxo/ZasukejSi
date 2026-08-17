<?php

namespace App\Filament\Resources\Profiles\RelationManagers;

use App\Models\ProfileEditLog;
use App\Support\RoleLabels;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * What has been changed on this profile, and by whom.
 *
 * Read-only on purpose: a log somebody can edit is not a log. There are no
 * create, edit or delete actions here at all.
 */
class EditLogRelationManager extends RelationManager
{
    protected static string $relationship = 'editLogs';

    protected static ?string $title = 'Log úprav';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Kdy')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Kdo')
                    // Změny z příkazové řádky a z importu nemají autora.
                    ->placeholder('systém')
                    ->description(fn (ProfileEditLog $record) => $record->user
                        ? RoleLabels::for($record->user->roles->first()?->name)
                        : 'automaticky'),

                TextColumn::make('fields')
                    ->label('Co se změnilo')
                    ->state(fn (ProfileEditLog $record) => $record->fieldList())
                    ->wrap(),

                TextColumn::make('detail')
                    ->label('Z čeho na co')
                    ->state(fn (ProfileEditLog $record) => collect($record->change_set ?? [])
                        ->map(fn (array $change, string $field) => sprintf(
                            '%s: %s → %s',
                            ProfileEditLog::fieldLabel($field),
                            ProfileEditLog::short($change['from'] ?? null),
                            ProfileEditLog::short($change['to'] ?? null),
                        ))
                        ->implode("\n"))
                    ->wrap()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->emptyStateHeading('Zatím žádná úprava')
            ->emptyStateDescription('Každé uložení profilu — vaše i provozovatelčino — se sem zapíše.');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
