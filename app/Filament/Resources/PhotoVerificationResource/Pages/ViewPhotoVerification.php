<?php

namespace App\Filament\Resources\PhotoVerificationResource\Pages;

use App\Filament\Resources\PhotoVerificationResource;
use App\Models\Notification as UserNotification;
use Filament\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ViewPhotoVerification extends ViewRecord
{
    protected static string $resource = PhotoVerificationResource::class;

    public function getTitle(): string
    {
        return __('filament.pages.photo_verifications.review_photo');
    }

    public function getSubheading(): ?string
    {
        $count = $this->getPendingCount();
        return trans_choice('filament.pages.photo_verifications.pending_count_message', $count, ['count' => $count]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Profile info as inline entries
                Section::make(__('filament.pages.photo_verifications.profile_info'))
                    ->schema([
                        TextEntry::make('profile_name')
                            ->label(__('filament.pages.photo_verifications.profile_name'))
                            ->getStateUsing(fn (Media $record): ?string => $record->model?->display_name)
                            ->size('lg')
                            ->weight('bold')
                            ->inlineLabel(),

                        TextEntry::make('email')
                            ->label(__('filament.attributes.email'))
                            ->getStateUsing(fn (Media $record): ?string => $record->model?->user?->email)
                            ->copyable()
                            ->inlineLabel(),

                        TextEntry::make('is_main')
                            ->label(__('filament.pages.photo_verifications.main_photo'))
                            ->getStateUsing(fn (Media $record): string => $record->getCustomProperty('is_main', false) 
                                ? __('filament.values.yes') 
                                : __('filament.values.no'))
                            ->badge()
                            ->color(fn (Media $record): string => $record->getCustomProperty('is_main', false) 
                                ? 'warning' 
                                : 'gray')
                            ->icon(fn (Media $record): ?string => $record->getCustomProperty('is_main', false) 
                                ? 'heroicon-s-star' 
                                : null)
                            ->inlineLabel(),

                        TextEntry::make('created_at')
                            ->label(__('filament.pages.photo_verifications.requested_at'))
                            ->dateTime('d.m.Y H:i')
                            ->inlineLabel(),
                    ])
                    ->columns(2)
                    ->compact(),

                // Large photo section - full width
                Section::make(__('filament.pages.photo_verifications.photo'))
                    ->schema([
                        ImageEntry::make('photo_url')
                            ->label('')
                            ->getStateUsing(fn (Media $record): string => $record->getUrl())
                            ->extraImgAttributes([
                                'class' => 'max-h-[70vh] w-auto mx-auto rounded-lg shadow-lg',
                                'style' => 'object-fit: contain;',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible(false),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label(__('filament.actions.edit'))
                ->icon(Heroicon::OutlinedPencilSquare)
                ->color('gray')
                ->url(fn (): string => PhotoVerificationResource::getUrl('edit', ['record' => $this->record])),

            Action::make('approve')
                ->label(__('filament.pages.photo_verifications.approve'))
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->size('lg')
                ->requiresConfirmation()
                ->modalHeading(__('filament.pages.photo_verifications.approve_confirm_title'))
                ->modalDescription(__('filament.pages.photo_verifications.approve_confirm_desc'))
                ->action(function (): void {
                    /** @var Media $record */
                    $record = $this->record;
                    
                    $record->setCustomProperty('verification_status', 'verified');
                    $record->setCustomProperty('verified_at', now()->toISOString());
                    $record->setCustomProperty('verified_by', auth()->id());
                    $record->save();

                    // Update profile verified_at if this is the main photo
                    if ($record->getCustomProperty('is_main', false) && $record->model) {
                        $record->model->update(['verified_at' => now()]);
                        
                        // Send notification to the user
                        if ($record->model->user_id) {
                            UserNotification::createForUser(
                                $record->model->user_id,
                                __('notifications.profile.verified_title'),
                                __('notifications.profile.verified_message'),
                                'success'
                            );
                        }
                    }

                    Notification::make()
                        ->title(__('filament.pages.photo_verifications.approved'))
                        ->success()
                        ->send();

                    $this->redirectToNextOrList();
                }),

            Action::make('reject')
                ->label(__('filament.pages.photo_verifications.reject'))
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->size('lg')
                ->requiresConfirmation()
                ->modalHeading(__('filament.pages.photo_verifications.reject_confirm_title'))
                ->modalDescription(__('filament.pages.photo_verifications.reject_confirm_desc'))
                ->action(function (): void {
                    /** @var Media $record */
                    $record = $this->record;
                    
                    $record->setCustomProperty('verification_status', 'rejected');
                    $record->setCustomProperty('rejected_at', now()->toISOString());
                    $record->setCustomProperty('rejected_by', auth()->id());
                    $record->save();

                    // Clear profile verified_at if this is the main photo
                    if ($record->getCustomProperty('is_main', false) && $record->model) {
                        $record->model->update(['verified_at' => null]);
                        
                        // Send notification to the user
                        if ($record->model->user_id) {
                            UserNotification::createForUser(
                                $record->model->user_id,
                                __('notifications.profile.unverified_title'),
                                __('notifications.profile.unverified_message'),
                                'danger'
                            );
                        }
                    }

                    Notification::make()
                        ->title(__('filament.pages.photo_verifications.rejected'))
                        ->warning()
                        ->send();

                    $this->redirectToNextOrList();
                }),
        ];
    }

    /**
     * Redirect to the next pending verification or back to the list.
     */
    protected function redirectToNextOrList(): void
    {
        // Find the next pending photo
        $nextPhoto = Media::query()
            ->where('collection_name', 'profile-images')
            ->whereJsonContains('custom_properties->verification_status', 'pending')
            ->where('id', '!=', $this->record->id)
            ->orderBy('created_at', 'asc')
            ->first();

        if ($nextPhoto) {
            $this->redirect(PhotoVerificationResource::getUrl('view', ['record' => $nextPhoto]));
        } else {
            $this->redirect(PhotoVerificationResource::getUrl('index'));
        }
    }

    protected function getPendingCount(): int
    {
        return Media::query()
            ->where('collection_name', 'profile-images')
            ->whereJsonContains('custom_properties->verification_status', 'pending')
            ->count();
    }
}
