<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Auth\Events\Verified;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verify_email')
                ->label('Verify Email')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => !$this->record->hasVerifiedEmail())
                ->requiresConfirmation()
                ->modalHeading('Verify Email Address')
                ->modalDescription('Are you sure you want to manually verify this user\'s email address?')
                ->modalSubmitActionLabel('Verify Email')
                ->action(function () {
                    $user = $this->record;
                    
                    if ($user->markEmailAsVerified()) {
                        // Cast to ensure type compatibility for the Verified event
                        if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
                            event(new Verified($user));
                        }
                        
                        Notification::make()
                            ->title('Email Verified')
                            ->body('The user\'s email address has been successfully verified.')
                            ->success()
                            ->send();
                            
                        // Refresh the page to update the button visibility
                        $this->redirect($this->getResource()::getUrl('edit', ['record' => $user]));
                    } else {
                        Notification::make()
                            ->title('Verification Failed')
                            ->body('The email address was already verified or could not be verified.')
                            ->warning()
                            ->send();
                    }
                }),
            DeleteAction::make(),
        ];
    }
}
