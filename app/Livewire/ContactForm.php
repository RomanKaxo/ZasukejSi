<?php

namespace App\Livewire;

use App\Models\ContactMessage;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * The contact page's form.
 *
 * A signed-in visitor has their name, e-mail and phone filled in for them, and
 * the message is tied to their account and profile — an admin answering it
 * then knows who is writing rather than having to trust the typed address.
 */
class ContactForm extends Component
{
    #[Validate('required|string|max:100')]
    public string $firstName = '';

    #[Validate('required|string|max:100')]
    public string $lastName = '';

    #[Validate('nullable|string|max:32')]
    public string $phone = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|string|min:10|max:5000')]
    public string $message = '';

    public bool $submitted = false;

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        // `users.name` is a single field, so the first space is the only split
        // available. The sender can correct it before sending.
        $parts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [];

        $this->firstName = $parts[0] ?? '';
        $this->lastName = $parts[1] ?? '';
        $this->email = (string) $user->email;
        $this->phone = (string) ($user->phone ?? '');
    }

    public function submit(): void
    {
        $throttleKey = 'contact-form|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('message', __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]));

            return;
        }

        $this->validate();

        RateLimiter::hit($throttleKey, 600);

        $user = auth()->user();

        ContactMessage::create([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'phone' => $this->phone !== '' ? $this->phone : null,
            'email' => $this->email,
            'message' => $this->message,
            'user_id' => $user?->id,
            'profile_id' => $user?->profile?->id,
            'status' => ContactMessage::STATUS_NEW,
            'locale' => app()->getLocale(),
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 1000),
        ]);

        $this->submitted = true;
        $this->reset(['message']);
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
