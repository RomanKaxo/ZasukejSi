<?php

namespace App\Livewire;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LoginModal extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    public bool $showModal = false;

    protected $listeners = ['show-login-modal' => 'show', 'hide-login-modal' => 'hide'];

    /**
     * Show the login modal
     */
    public function show()
    {
        $this->showModal = true;
        $this->dispatch('modal-opened', 'login');
    }

    /**
     * Hide the login modal
     */
    public function hide()
    {
        // Immediately hide modal without server round trip
        $this->showModal = false;
        
        // Reset form data without triggering loading states
        $this->email = '';
        $this->password = '';
        $this->remember = false;
        
        $this->resetErrorBag();
        $this->dispatch('modal-closed', 'login');
    }

    /**
     * Attempt to authenticate the request's credentials.
     */
    public function authenticate(): void
    {
        try {
            $this->ensureIsNotRateLimited();

            if (! Auth::attempt($this->only(['email', 'password']), $this->remember)) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }

            RateLimiter::clear($this->throttleKey());

            session()->regenerate();

            // Hide modal immediately
            $this->showModal = false;
            
            // Redirect to the referring page or profiles index
            $this->redirect(request()->header('Referer') ?: route('profiles.index'));
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->addError('login', __('auth.login.error'));
        }
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }

    public function render()
    {
        return view('livewire.login-modal');
    }
}