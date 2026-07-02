<?php

namespace App\Livewire;

use App\Actions\Auth\RegisterUser;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Validate;
use Livewire\Component;

class RegisterModal extends Component
{
    // Modal state
    public bool $showModal = false;
    public int $currentStep = 1;
    public int $totalSteps = 3;
    public bool $registrationSuccess = false;
    public string $registeredEmail = '';

    // Step 1: Gender selection
    #[Validate('required|in:male,female')]
    public string $gender = '';

    // Step 2: Registration form
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|email|max:255|unique:users')]
    public string $email = '';

    #[Validate('nullable|string|max:20')]
    public string $phone = '';

    #[Validate('required|string|min:8')]
    public string $password = '';

    #[Validate('required|string|same:password')]
    public string $password_confirmation = '';

    protected $listeners = ['show-register-modal' => 'show', 'hide-register-modal' => 'hide'];

    /**
     * Show the registration modal
     */
    public function show()
    {
        $this->showModal = true;
        $this->currentStep = 1;
        $this->dispatch('modal-opened', 'register');
    }

    /**
     * Hide the registration modal
     */
    public function hide()
    {
        // Immediately hide modal without server round trip
        $this->showModal = false;
        
        // Reset form data without triggering loading states
        $this->currentStep = 1;
        $this->registrationSuccess = false;
        $this->registeredEmail = '';
        $this->gender = '';
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->password = '';
        $this->password_confirmation = '';
        
        $this->resetErrorBag();
        $this->dispatch('modal-closed', 'register');
    }

    /**
     * Go to next step
     */
    public function nextStep()
    {
        if ($this->currentStep === 1) {
            $this->validateOnly('gender');
        }

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    /**
     * Go to previous step
     */
    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    /**
     * Select gender and proceed to next step
     */
    public function selectGender($gender)
    {
        $this->gender = $gender;
        $this->nextStep();
    }

    /**
     * Register a new user
     */
    public function register()
    {
        // Validate all fields at once for better performance
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', Rules\Password::defaults()],
            'password_confirmation' => ['required', 'string', 'same:password'],
            'gender' => ['required', 'in:male,female'],
        ]);

        try {
            $user = $this->registerUserAction()->execute([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => !empty($validated['phone']) ? $validated['phone'] : null,
                'gender' => $validated['gender'],
                'password' => $validated['password'],
            ]);

            // Fire the registered event
            event(new Registered($user));

            // Store email for success message and move to success step
            $this->registeredEmail = $validated['email'];
            $this->registrationSuccess = true;
            $this->currentStep = 3;
            
        } catch (\Exception $e) {
            $this->addError('registration', __('auth.register.error'));
        }
    }

    protected function registerUserAction(): RegisterUser
    {
        return app(RegisterUser::class);
    }

    /**
     * Get the progress percentage
     */
    public function getProgressPercentage()
    {
        return round(($this->currentStep / $this->totalSteps) * 100);
    }

    /**
     * Get masked email for display (e.g., eva******@gmail.com)
     */
    public function getMaskedEmailProperty()
    {
        if (empty($this->registeredEmail)) {
            return '';
        }

        $email = $this->registeredEmail;
        [$username, $domain] = explode('@', $email);

        // Show first 3 characters, mask the rest
        $visibleLength = min(3, strlen($username));
        $maskedUsername = substr($username, 0, $visibleLength) . str_repeat('*', max(0, strlen($username) - $visibleLength));

        return $maskedUsername . '@' . $domain;
    }

    public function render()
    {
        return view('livewire.register-modal');
    }
}