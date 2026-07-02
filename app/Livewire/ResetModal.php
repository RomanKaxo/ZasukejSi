<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ResetModal extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    /**
     * Whether to show success message
     */
    public bool $showSuccessMessage = false;

    public bool $showModal = false;
    public bool $emailSent = false;

    protected $listeners = ['show-reset-modal' => 'show', 'hide-reset-modal' => 'hide'];

    /**
     * Show the reset modal
     */
    public function show()
    {
        $this->showModal = true;
        $this->emailSent = false;
        $this->dispatch('modal-opened', 'reset');
    }

    /**
     * Hide the reset modal
     */
    public function hide()
    {
        // Immediately hide modal without server round trip
        $this->showModal = false;
        $this->emailSent = false;
        
        // Reset form data without triggering loading states
        $this->email = '';
        
        $this->resetErrorBag();
        $this->dispatch('modal-closed', 'reset');
    }

    /**
     * Send password reset link
     */
    public function sendResetLink()
    {
        $this->validate();

        try {
            // We send the password reset link using Laravel's built-in functionality
            $status = Password::sendResetLink(
                $this->only(['email'])
            );

            if ($status === Password::RESET_LINK_SENT) {
                $this->resetForm();
                $this->showSuccessMessage = true;

                // Log successful password reset request
                Log::info('Password reset link sent successfully', [
                    'email' => $this->email,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                // Auto-close modal after 5 seconds
                $this->dispatch('auto-close-modal', delay: 5000);
            } else {
                // Handle different error statuses
                $this->addError('email', __($status));
                
                Log::warning('Password reset link failed to send', [
                    'email' => $this->email,
                    'status' => $status,
                    'ip' => request()->ip()
                ]);
            }
        } catch (\Exception $e) {
            $this->addError('email', __('front.reset_modal.error_message'));
            
            Log::error('Password reset exception', [
                'email' => $this->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Back to login modal
     */
    public function backToLogin()
    {
        $this->hide();
        $this->dispatch('show-login-modal');
    }

    public function render()
    {
        return view('livewire.reset-modal');
    }
}