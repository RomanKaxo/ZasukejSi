<?php

namespace App\Filament\Resources\Profiles\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // A listing of names alone: an admin recognises a profile by
                // its photo, and a profile with no photo at all is worth
                // spotting without opening it.
                ImageColumn::make('thumbnail')
                    ->label('')
                    ->getStateUsing(fn ($record) => $record->getFirstImageThumbUrl())
                    ->height(56)
                    ->extraImgAttributes(['style' => 'object-fit:cover;border-radius:6px;width:42px;height:56px;'])
                    ->default(null),

                TextColumn::make('display_name')
                    ->label(__('profiles.table.display_name'))
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        $currentLocale = app()->getLocale();
                        $fallbackLocale = config('app.fallback_locale', 'en');
                        
                        // Try to get translation for current locale, fallback to English if not available
                        return $record->getTranslation('display_name', $currentLocale) 
                            ?: $record->getTranslation('display_name', $fallbackLocale)
                            ?: $record->display_name; // Final fallback
                    }),

                TextColumn::make('user.name')
                    ->label(__('profiles.table.user'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.gender')
                    ->label(__('profiles.table.gender'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'male' => 'blue',
                        'female' => 'pink',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ? __("profiles.gender.{$state}") : '-'),

                TextColumn::make('age')
                    ->label(__('profiles.table.age'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('profiles.table.status'))
                    ->badge()
                    // A match without a default throws on any status the list
                    // does not name, which took the whole listing down with a
                    // 500 rather than showing one odd row. Statuses come from a
                    // database column, so the set can always grow.
                    ->color(fn (?string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(function (?string $state): string {
                        if ($state === null || $state === '') {
                            return '—';
                        }

                        $key = "profiles.status.{$state}";
                        $label = __($key);

                        // An unknown status has no translation; show the raw
                        // value rather than the key.
                        return $label === $key ? $state : $label;
                    }),

                IconColumn::make('is_public')
                    ->label(__('profiles.table.public'))
                    ->boolean(),

                // Whether a profile is paying was only visible inside the
                // record, on a tab.
                TextColumn::make('vip')
                    ->label('VIP')
                    ->state(fn ($record) => $record->isVip() ? 'VIP' : '—')
                    ->badge()
                    ->color(fn ($record) => $record->isVip() ? 'warning' : 'gray')
                    // A relation, not a model: `activeSubscription()` returns
                    // the HasOne itself.
                    ->tooltip(fn ($record) => $record->activeSubscription?->ends_at?->format('d.m.Y')),

                TextColumn::make('photos')
                    ->label(__('profiles.table.photos'))
                    ->state(fn ($record) => $record->getAllImages()->count())
                    // No photos means the profile cannot really go live.
                    ->color(fn ($record) => $record->getAllImages()->isEmpty() ? 'danger' : null)
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('rating')
                    ->label(__('profiles.table.rating'))
                    ->state(fn ($record) => $record->getTotalRatings() > 0
                        ? number_format($record->getAverageRating(), 1) . ' (' . $record->getTotalRatings() . ')'
                        : '—')
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('segments.name')
                    ->label(__('common.Segments'))
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->allSegments()->pluck('name')),

                TextColumn::make('created_at')
                    ->label(__('profiles.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('profiles.table.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('profiles.filters.status'))
                    ->options([
                        'draft' => __('profiles.status.draft'),
                        'pending' => __('profiles.status.pending'),
                        'approved' => __('profiles.status.approved'),
                        'rejected' => __('profiles.status.rejected'),
                    ]),

                SelectFilter::make('city')
                    ->label(__('profiles.filters.city'))
                    ->searchable()
                    ->options(function () {
                        return \App\Models\Profile::whereNotNull('city')
                            ->pluck('city', 'city')
                            ->unique()
                            ->sort();
                    }),

                SelectFilter::make('segments')
                    ->label(__('profiles.filters.segment'))
                    ->relationship('segments', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', app()->getLocale())),

                SelectFilter::make('country_code')
                    ->label(__('profiles.filters.country'))
                    ->options(function () {
                        $codes = include base_path('lang/en/codes.php');
                        // Only show codes that are present in profiles
                        $usedCodes = \App\Models\Profile::whereNotNull('country_code')->pluck('country_code')->unique();
                        return collect($codes)
                            ->only($usedCodes)
                            ->mapWithKeys(fn($name, $code) => [strtolower($code) => $name]);
                    })
                    ->searchable(),

                SelectFilter::make('user.gender')
                    ->label(__('profiles.filters.gender'))
                    ->relationship('user', 'gender')
                    ->options([
                        'male' => __('profiles.gender.male'),
                        'female' => __('profiles.gender.female'),
                    ]),

                // The three questions an admin opens this screen to answer,
                // each of which previously meant scrolling the whole list.
                Filter::make('waiting')
                    ->label(__('profiles.filters.waiting'))
                    ->query(fn ($query) => $query->where('status', 'pending')),

                Filter::make('without_photos')
                    ->label(__('profiles.filters.without_photos'))
                    ->query(fn ($query) => $query->whereDoesntHave('media', fn ($q) => $q->where('collection_name', 'profile-images'))),

                Filter::make('vip')
                    ->label(__('profiles.filters.vip'))
                    ->query(fn ($query) => $query->whereHas('subscriptions', fn ($q) => $q
                        ->where('status', 'active')
                        ->where(function ($dates) {
                            $dates->whereNull('ends_at')->orWhere('ends_at', '>', now());
                        }))),

                TrashedFilter::make(),
            ])
            ->recordActions([
                // Approving and blocking were only reachable by opening the
                // record and changing a select — the two decisions an admin
                // makes most often needed the most clicks.
                \Filament\Actions\Action::make('approve')
                    ->label(__('profiles.actions.approve'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'approved')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update([
                        'status' => 'approved',
                        'verified_at' => $record->verified_at ?? now(),
                    ])),

                \Filament\Actions\Action::make('block')
                    ->label(__('profiles.actions.block'))
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status !== 'rejected')
                    ->requiresConfirmation()
                    ->modalDescription(__('profiles.actions.block_description'))
                    // Blocking also takes it off the site; leaving it public
                    // would make the status a label with no effect.
                    ->action(fn ($record) => $record->update([
                        'status' => 'rejected',
                        'is_public' => false,
                    ])),

                \Filament\Actions\ViewAction::make(),
                EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Approving a batch of scraped imports one by one was the
                    // slowest part of the whole import flow.
                    BulkAction::make('approveSelected')
                        ->label(__('profiles.actions.approve'))
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'approved',
                                    'verified_at' => $record->verified_at ?? now(),
                                ]);
                            }
                        }),

                    BulkAction::make('publishSelected')
                        ->label(__('profiles.actions.publish'))
                        ->icon('heroicon-o-globe-alt')
                        ->color('primary')
                        ->requiresConfirmation()
                        // Publishing an unapproved profile would put it on the
                        // site ahead of its own review.
                        ->modalDescription(__('profiles.actions.publish_description'))
                        ->action(fn (Collection $records) => $records
                            ->where('status', 'approved')
                            ->each
                            ->update(['is_public' => true])),

                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
