<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoVerificationResource\Pages;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PhotoVerificationResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCamera;

    protected static ?int $navigationSort = 15;

    protected static ?string $slug = 'photo-verifications';

    public static function getNavigationLabel(): string
    {
        return __('filament.navigation.photo_verifications');
    }

    public static function getModelLabel(): string
    {
        return __('filament.pages.photo_verifications.photo');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.pages.photo_verifications.title');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Media::query()
            ->where('collection_name', 'profile-images')
            ->whereJsonContains('custom_properties->verification_status', 'pending')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('collection_name', 'profile-images')
            ->whereRaw("JSON_EXTRACT(custom_properties, '$.verification_status') IS NOT NULL")
            ->with(['model.user'])
            ->orderBy('created_at', 'desc');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('url')
                    ->label(__('filament.pages.photo_verifications.photo'))
                    ->getStateUsing(fn (Media $record): string => $record->getUrl())
                    ->height(120)
                    ->width(90),

                TextColumn::make('model.display_name')
                    ->label(__('filament.pages.photo_verifications.profile_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('model.user.email')
                    ->label(__('filament.attributes.email'))
                    ->searchable(),

                TextColumn::make('custom_properties.is_main')
                    ->label(__('filament.pages.photo_verifications.main_photo'))
                    ->formatStateUsing(fn ($state) => $state ? '⭐ ' . __('filament.values.yes') : __('filament.values.no'))
                    ->badge()
                    ->color(fn ($state) => $state ? 'warning' : 'gray'),

                TextColumn::make('created_at')
                    ->label(__('filament.pages.photo_verifications.requested_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('custom_properties.verification_status')
                    ->label(__('filament.pages.photo_verifications.verification_status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => __('filament.pages.photo_verifications.status.' . ($state ?? 'pending')))
                    ->color(fn ($state) => match ($state) {
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('verification_status')
                    ->label(__('filament.pages.photo_verifications.verification_status'))
                    ->options([
                        'pending' => __('filament.pages.photo_verifications.status.pending'),
                        'verified' => __('filament.pages.photo_verifications.status.verified'),
                        'rejected' => __('filament.pages.photo_verifications.status.rejected'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! $data['value']) {
                            return $query;
                        }
                        return $query->whereJsonContains('custom_properties->verification_status', $data['value']);
                    })
                    ->default('pending'),
            ])
            ->actions([
                Action::make('review')
                    ->label(__('filament.pages.photo_verifications.review'))
                    ->icon(Heroicon::OutlinedEye)
                    ->color('primary')
                    ->url(fn (Media $record): string => static::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->label(__('filament.actions.edit')),
            ])
            ->bulkActions([
                BulkAction::make('approveSelected')
                    ->label(__('filament.pages.photo_verifications.approve_selected'))
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        foreach ($records as $record) {
                            $record->setCustomProperty('verification_status', 'verified');
                            $record->setCustomProperty('verified_at', now()->toISOString());
                            $record->setCustomProperty('verified_by', auth()->id());
                            $record->save();

                            if ($record->getCustomProperty('is_main', false) && $record->model) {
                                $record->model->update(['verified_at' => now()]);
                            }
                        }

                        Notification::make()
                            ->title(__('filament.pages.photo_verifications.bulk_approved', ['count' => $records->count()]))
                            ->success()
                            ->send();
                    }),

                BulkAction::make('rejectSelected')
                    ->label(__('filament.pages.photo_verifications.reject_selected'))
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        foreach ($records as $record) {
                            $record->setCustomProperty('verification_status', 'rejected');
                            $record->setCustomProperty('rejected_at', now()->toISOString());
                            $record->setCustomProperty('rejected_by', auth()->id());
                            $record->save();

                            if ($record->getCustomProperty('is_main', false) && $record->model) {
                                $record->model->update(['verified_at' => null]);
                            }
                        }

                        Notification::make()
                            ->title(__('filament.pages.photo_verifications.bulk_rejected', ['count' => $records->count()]))
                            ->warning()
                            ->send();
                    }),
            ])
            ->emptyStateHeading(__('filament.pages.photo_verifications.no_pending'))
            ->emptyStateDescription(__('filament.pages.photo_verifications.no_pending_desc'))
            ->emptyStateIcon(Heroicon::OutlinedCheckCircle)
            ->recordUrl(fn (Media $record): string => static::getUrl('view', ['record' => $record]));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhotoVerifications::route('/'),
            'view' => Pages\ViewPhotoVerification::route('/{record}'),
            'edit' => Pages\EditPhotoVerification::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
}
