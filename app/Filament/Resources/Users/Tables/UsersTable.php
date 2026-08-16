<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament.attributes.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label(__('filament.attributes.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->label(__('filament.attributes.phone'))
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('gender')
                    ->label(__('filament.attributes.gender'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'male' => 'blue',
                        'female' => 'pink',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'male' => __('filament.attributes.gender_male'),
                        'female' => __('filament.attributes.gender_female'),
                        default => '-',
                    }),

                IconColumn::make('email_verified_at')
                    ->label(__('filament.attributes.email_verified'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                // The profile was a yes/no icon; whose profile it is, and
                // whether it is even published, took another screen.
                TextColumn::make('profile_link')
                    ->label(__('filament.attributes.profile'))
                    ->state(fn ($record) => $record->profile?->display_name ?? '—')
                    ->description(fn ($record) => $record->profile
                        ? ($record->profile->is_public ? __('filament.attributes.published') : __('filament.attributes.unpublished'))
                        : null)
                    ->url(fn ($record) => $record->profile
                        ? route('filament.admin.resources.profiles.view', $record->profile)
                        : null)
                    ->color(fn ($record) => $record->profile ? 'info' : 'gray'),

                // Whether a member is paying — the reason most support
                // questions about an account get asked.
                TextColumn::make('membership')
                    ->label(__('filament.attributes.membership'))
                    ->state(fn ($record) => $record->hasActiveMembership()
                        ? __('filament.attributes.membership_active')
                        : '—')
                    ->badge()
                    ->color(fn ($record) => $record->hasActiveMembership() ? 'success' : 'gray')
                    ->tooltip(fn ($record) => $record->membershipEndsAt()?->format('d.m.Y'))
                    ->toggleable(),

                TextColumn::make('roles.name')
                    ->label(__('filament.attributes.roles'))
                    ->badge()
                    ->separator(','),

                TextColumn::make('created_at')
                    ->label(__('filament.attributes.created_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label(__('filament.attributes.roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),

                SelectFilter::make('gender')
                    ->label(__('filament.attributes.gender'))
                    ->options([
                        'female' => __('filament.attributes.gender_female'),
                        'male' => __('filament.attributes.gender_male'),
                    ]),

                Filter::make('unverified')
                    ->label(__('filament.attributes.unverified'))
                    ->query(fn ($query) => $query->whereNull('email_verified_at')),

                Filter::make('without_profile')
                    ->label(__('filament.attributes.without_profile'))
                    ->query(fn ($query) => $query->whereDoesntHave('profile')),
            ])
            ->recordActions([
                // Confirming an address by hand is a routine support task that
                // had no button at all.
                Action::make('verifyEmail')
                    ->label(__('filament.attributes.verify_email'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->email_verified_at === null)
                    ->action(fn ($record) => $record->forceFill(['email_verified_at' => now()])->save()),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
