<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Show the user account dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $user->load(['profile', 'roles', 'permissions']);
        
        return view('account.dashboard', compact('user'));
    }

    /**
     * Show the user profile edit form.
     */
    public function edit()
    {
        $user = Auth::user();
        $user->load('profile');
        
        return view('account.profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone,' . $user->id],
        ]);

        $user->fill($request->only(['name', 'email', 'phone']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('account.edit')->with('status', 'profile-updated');
    }

    /**
     * Show the password change form.
     */
    public function showPasswordForm()
    {
        return view('account.password.edit');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('account.dashboard')->with('status', 'password-updated');
    }

    /**
     * Show user permissions and roles.
     */
    public function showPermissions()
    {
        $user = Auth::user();
        $user->load(['roles.permissions', 'permissions']);

        // Get all permissions - both direct and through roles
        $allPermissions = $user->getAllPermissions();
        $directPermissions = $user->permissions;
        $rolePermissions = $user->getPermissionsViaRoles();

        return view('account.permissions', compact('user', 'allPermissions', 'directPermissions', 'rolePermissions'));
    }

    /**
     * Show photos management page.
     */
    public function showPhotos()
    {
        return view('account.photos');
    }

    /**
     * Show services management page.
     */
    public function showServices()
    {
        return view('account.services');
    }

    /**
     * Show statistics page.
     */
    public function showStatistics()
    {
        return view('account.statistics');
    }

    /**
     * Show reviews page.
     */
    public function showReviews()
    {
        // return view('account.reviews');
    }

    /**
     * Delete user account.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}