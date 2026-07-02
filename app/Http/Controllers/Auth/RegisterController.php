<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct(
        private readonly RegisterUser $registerUser,
    ) {}

    /**
     * Where to redirect users after registration.
     */
    protected string $redirectTo = '/account';

    /**
     * Show the application registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        // Try to send verification email, but don't let it break registration
        try {
            event(new Registered($user));
        } catch (\Exception $e) {
            // Log email sending failure (without PII) but continue with registration
            Log::error('Failed to send verification email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            session()->flash('warning', __('auth.verification_email_failed'));
        }

        Auth::login($user);

        // Redirect based on gender
        if ($user->isMale()) {
            return redirect('/account/member');
        }

        return redirect($this->redirectTo);
    }


    /**
     * Get a validator for an incoming registration request.
     */
    protected function validator(array $data)
    {
        return validator($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users'],
            'gender' => ['required', 'in:male,female'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     */
    protected function create(array $data)
    {
        return $this->registerUser->execute($data);
    }
}