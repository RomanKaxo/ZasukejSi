<?php

namespace App\Filament\Resources\Translations\Tables;

use App\Models\Translation;
use App\Support\Locales;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class TranslationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('locale')
                    ->label(__('translations.table.locale'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Locales::nativeName($state))
                    ->sortable(),

                TextColumn::make('full_key')
                    ->label(__('translations.table.key'))
                    ->getStateUsing(fn (Translation $record) => $record->full_key)
                    ->searchable(query: fn ($query, string $search) => $query
                        ->where('key', 'like', "%{$search}%")
                        ->orWhere('group', 'like', "%{$search}%"))
                    ->description(fn (Translation $record) => $record->group)
                    ->wrap(),

                // Edited straight in the list: retranslating a site is a lot of
                // small edits, and opening a form for each would be needless.
                TextInputColumn::make('value')
                    ->label(__('translations.table.value'))
                    ->searchable()
                    ->rules(['nullable', 'string', 'max:5000']),

                TextColumn::make('default_value')
                    ->label(__('translations.table.default'))
                    ->limit(60)
                    ->tooltip(fn (Translation $record) => $record->default_value)
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('common.Created'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('key')
            ->filters([
                // Defaults to the language the admin is working in, so the list
                // opens on one mutation instead of every row three times over.
                SelectFilter::make('locale')
                    ->label(__('translations.table.locale'))
                    ->options(collect(Locales::all())->map(fn ($m) => $m['native'])->all())
                    ->default(app()->getLocale()),

                SelectFilter::make('group')
                    ->label(__('translations.table.group'))
                    ->options(fn () => Translation::query()
                        ->distinct()
                        ->orderBy('group')
                        ->pluck('group', 'group')
                        ->all()),

                Filter::make('overridden')
                    ->label(__('translations.filter.overridden'))
                    ->query(fn ($query) => $query->overridden()),

                Filter::make('untranslated')
                    ->label(__('translations.filter.untranslated'))
                    // Still identical to the source-language default — i.e.
                    // nobody has translated it for this locale yet.
                    ->query(fn ($query) => $query->whereColumn('value', 'default_value')),
            ])
            ->recordActions([
                // One key, every language, saved in a single step. Editing row
                // by row meant hunting down the same key once per locale.
                Action::make('translateAll')
                    ->label(__('translations.actions.all_locales'))
                    ->icon('heroicon-o-language')
                    ->color('primary')
                    ->modalWidth('2xl')
                    ->fillForm(function (Translation $record) {
                        $siblings = Translation::query()
                            ->where('group', $record->group)
                            ->where('key', $record->key)
                            ->pluck('value', 'locale');

                        $data = [];

                        foreach (Locales::codes() as $locale) {
                            $data[$locale] = $siblings[$locale] ?? null;
                        }

                        return $data;
                    })
                    ->form(fn (Translation $record) => collect(Locales::codes())
                        ->map(fn (string $locale) => \Filament\Forms\Components\Textarea::make($locale)
                            ->label(Locales::nativeName($locale))
                            ->rows(2)
                            ->maxLength(5000)
                            // The source string, so the translator can see what
                            // they are translating without leaving the modal.
                            ->helperText($locale === $record->locale ? $record->default_value : null))
                        ->all())
                    ->action(function (Translation $record, array $data) {
                        foreach ($data as $locale => $value) {
                            if (! Locales::isSupported($locale)) {
                                continue;
                            }

                            Translation::updateOrCreate(
                                ['locale' => $locale, 'group' => $record->group, 'key' => $record->key],
                                ['value' => $value, 'default_value' => $record->default_value],
                            );
                        }

                        Translation::flushCache();

                        \Filament\Notifications\Notification::make()
                            ->title(__('translations.actions.all_locales_saved'))
                            ->success()
                            ->send();
                    }),

                EditAction::make(),

                Action::make('reset')
                    ->label(__('translations.actions.reset'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Translation $record) => $record->isOverridden())
                    ->action(fn (Translation $record) => $record->update(['value' => $record->default_value])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('reset')
                        ->label(__('translations.actions.reset_selected'))
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(fn (Translation $record) => $record->update([
                                'value' => $record->default_value,
                            ]));
                        }),
                ]),
            ]);
    }
}
