<?php

namespace App\Filament\Resources\PhotoVerificationResource\Pages;

use App\Filament\Resources\PhotoVerificationResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EditPhotoVerification extends EditRecord
{
    protected static string $resource = PhotoVerificationResource::class;

    public function getTitle(): string
    {
        return __('filament.pages.photo_verifications.edit_verification');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament.pages.photo_verifications.profile_info'))
                    ->schema([
                        TextEntry::make('profile_name')
                            ->label(__('filament.pages.photo_verifications.profile_name'))
                            ->getStateUsing(fn (Media $record): ?string => $record->model?->display_name)
                            ->inlineLabel(),

                        TextEntry::make('email')
                            ->label(__('filament.attributes.email'))
                            ->getStateUsing(fn (Media $record): ?string => $record->model?->user?->email)
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
                            ->inlineLabel(),

                        TextEntry::make('created_at')
                            ->label(__('filament.pages.photo_verifications.requested_at'))
                            ->dateTime('d.m.Y H:i')
                            ->inlineLabel(),
                    ])
                    ->columns(2)
                    ->compact(),

                Section::make(__('filament.pages.photo_verifications.photo'))
                    ->schema([
                        ImageEntry::make('photo_url')
                            ->label('')
                            ->getStateUsing(fn (Media $record): string => $record->getUrl())
                            ->extraImgAttributes([
                                'class' => 'max-h-[50vh] w-auto mx-auto rounded-lg shadow-lg',
                                'style' => 'object-fit: contain;',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make(__('filament.pages.photo_verifications.change_status'))
                    ->schema([
                        Select::make('verification_status')
                            ->label(__('filament.pages.photo_verifications.verification_status'))
                            ->options([
                                'pending' => __('filament.pages.photo_verifications.status.pending'),
                                'verified' => __('filament.pages.photo_verifications.status.verified'),
                                'rejected' => __('filament.pages.photo_verifications.status.rejected'),
                            ])
                            ->required()
                            ->native(false),
                    ]),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Media $record */
        $record = $this->record;

        $data['verification_status'] = $record->getCustomProperty('verification_status', 'pending');

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var Media $record */
        $record = $this->record;

        $newStatus = $data['verification_status'];
        $oldStatus = $record->getCustomProperty('verification_status', 'pending');

        $record->setCustomProperty('verification_status', $newStatus);

        if ($newStatus === 'verified' && $oldStatus !== 'verified') {
            $record->setCustomProperty('verified_at', now()->toISOString());
            $record->setCustomProperty('verified_by', auth()->id());
            $record->forgetCustomProperty('rejected_at');
            $record->forgetCustomProperty('rejected_by');

            // Update profile verified_at if this is the main photo
            if ($record->getCustomProperty('is_main', false) && $record->model) {
                $record->model->update(['verified_at' => now()]);
            }
        } elseif ($newStatus === 'rejected' && $oldStatus !== 'rejected') {
            $record->setCustomProperty('rejected_at', now()->toISOString());
            $record->setCustomProperty('rejected_by', auth()->id());
            $record->forgetCustomProperty('verified_at');
            $record->forgetCustomProperty('verified_by');

            // Clear profile verified_at if this is the main photo
            if ($record->getCustomProperty('is_main', false) && $record->model) {
                $record->model->update(['verified_at' => null]);
            }
        } elseif ($newStatus === 'pending') {
            $record->forgetCustomProperty('verified_at');
            $record->forgetCustomProperty('verified_by');
            $record->forgetCustomProperty('rejected_at');
            $record->forgetCustomProperty('rejected_by');

            // Clear profile verified_at if this is the main photo
            if ($record->getCustomProperty('is_main', false) && $record->model) {
                $record->model->update(['verified_at' => null]);
            }
        }

        $record->save();

        // We've already saved, so return empty data to prevent double save
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label(__('filament.actions.view'))
                ->icon(Heroicon::OutlinedEye)
                ->url(fn (): string => PhotoVerificationResource::getUrl('view', ['record' => $this->record])),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__('filament.pages.photo_verifications.status_updated'))
            ->success();
    }

    protected function getRedirectUrl(): string
    {
        return PhotoVerificationResource::getUrl('index');
    }
}
