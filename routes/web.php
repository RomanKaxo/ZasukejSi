<?php

use App\Http\Controllers\Auth\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/storage/{media}/{filename}', [MediaController::class, 'show'])
    ->whereNumber('media')
    ->name('media.storage');
Route::get('/media/{media}/{filename}', [MediaController::class, 'show'])->name('media.show');
Route::get('/', [ProfileController::class, 'index'])->name('profiles.index');
Route::get('/profiles/{id}', [ProfileController::class, 'show'])->name('profiles.show');
Route::get('/countries', function () {
    $girlsCount = \App\Models\User::where('gender', 'female')->count();
    $gentsCount = \App\Models\User::where('gender', 'male')->count();
    
    return view('countries.index', compact('girlsCount', 'gentsCount'));
})->name('countries.index');

// API routes for AJAX/Alpine.js
Route::prefix('api')->middleware('throttle:60,1')->group(function () {
    Route::get('/profiles', [ProfileController::class, 'api'])->name('api.profiles');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Register Routes
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Password Reset Routes
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::post('/forgot-password', function (Request $request) {
        $request->validate(['email' => 'required|email']);

        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );

        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    })->name('password.email');

    Route::get('/reset-password/{token}', function (string $token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');

    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => \Illuminate\Support\Facades\Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    })->name('password.update');
});

// Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');
});

// Email verification link - works without being logged in
Route::get('/email/verify/{id}/{hash}', function (Request $request) {
    $user = User::findOrFail($request->id);
    
    if (! hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Invalid verification link.');
    }

    if ($user->hasVerifiedEmail()) {
        return redirect()->route('login')->with('status', 'email-already-verified');
    }

    if ($user->markEmailAsVerified()) {
        event(new \Illuminate\Auth\Events\Verified($user));
    }

    return redirect()->route('login')->with('status', 'email-verified');
})->middleware('signed')->name('verification.verify');

// Logout Route (authenticated users only)
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Notifications Routes
Route::middleware(['auth', 'verified'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/archived', [App\Http\Controllers\NotificationController::class, 'archived'])->name('archived');
    Route::post('/{notification}/archive', [App\Http\Controllers\NotificationController::class, 'archive'])->name('archive');
    Route::delete('/{notification}', [App\Http\Controllers\NotificationController::class, 'delete'])->name('delete');
    Route::post('/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
});

// Messages Routes
Route::middleware(['auth', 'verified'])->prefix('messages')->name('messages.')->group(function () {
    Route::get('/', [App\Http\Controllers\MessageController::class, 'inbox'])->name('index');
    Route::get('/{user}', [App\Http\Controllers\MessageController::class, 'show'])->name('show');
    Route::post('/{user}', [App\Http\Controllers\MessageController::class, 'store'])->name('store');
});

// Account Routes (authenticated users only - female users for profile management)
Route::middleware(['auth', 'verified'])->prefix('account')->name('account.')->group(function () {
    // Dashboard - redirects based on gender
    Route::get('/', function () {
        $user = auth()->user();
        if ($user->isMale() && !$user->hasRole('admin')) {
            return redirect()->route('account.member.dashboard');
        }
        return app(AccountController::class)->index();
    })->name('dashboard');
    
    // Profile Management (female users only)
    Route::get('/profile', [AccountController::class, 'edit'])->name('edit');
    Route::patch('/profile', [AccountController::class, 'update'])->name('update');
    
    // Password Management
    Route::get('/password', [AccountController::class, 'showPasswordForm'])->name('password.edit');
    Route::patch('/password', [AccountController::class, 'updatePassword'])->name('password.update');
    
    // Additional Account Sections (female users only, require profile)
    Route::middleware(['gender:female', 'profile.exists'])->group(function () {
        Route::get('/photos', [AccountController::class, 'showPhotos'])->name('photos');
        Route::get('/services', [AccountController::class, 'showServices'])->name('services');
        Route::get('/statistics', [AccountController::class, 'showStatistics'])->name('statistics');
        Route::get('/reviews', [AccountController::class, 'showReviews'])->name('reviews');
    });
    
    // Delete Account
    Route::delete('/profile', [AccountController::class, 'destroy'])->name('destroy');
});

// Member Routes (authenticated male users)
Route::middleware(['auth', 'verified'])->prefix('account/member')->name('account.member.')->group(function () {
    // Dashboard / User Settings
    Route::get('/', [\App\Http\Controllers\Auth\MemberController::class, 'dashboard'])->name('dashboard');
    Route::patch('/settings', [\App\Http\Controllers\Auth\MemberController::class, 'updateSettings'])->name('settings.update');
    
    // Password Management
    Route::get('/password', [\App\Http\Controllers\Auth\MemberController::class, 'showPasswordForm'])->name('password.edit');
    Route::patch('/password', [\App\Http\Controllers\Auth\MemberController::class, 'updatePassword'])->name('password.update');
    
    // Member Features (to be implemented)
    Route::get('/ratings', [\App\Http\Controllers\Auth\MemberController::class, 'ratings'])->name('ratings');
    Route::get('/favorites', [\App\Http\Controllers\Auth\MemberController::class, 'favorites'])->name('favorites');
    Route::delete('/favorites/{profile}', [\App\Http\Controllers\Auth\MemberController::class, 'removeFavorite'])->name('favorites.remove');
    Route::get('/girls-of-month', [\App\Http\Controllers\Auth\MemberController::class, 'girlsOfMonth'])->name('girls-of-month');
    Route::get('/archive', [\App\Http\Controllers\Auth\MemberController::class, 'archive'])->name('archive');
    Route::get('/reported', [\App\Http\Controllers\Auth\MemberController::class, 'reported'])->name('reported');
});

// Test route for component system
Route::get('/test-components', function () {
    return view('test-components');
})->name('test.components');

// Preview route for the dashboard (added for testing)
Route::get('/preview/dashboard', function () {
    return view('account.dashboard_preview');
})->name('preview.dashboard');

// Preview route for photos page (added for testing)
Route::get('/preview/photos', function () {
    return view('account.photos_preview');
})->name('preview.photos');

// Preview route for services page (added for testing)
Route::get('/preview/services', function () {
    return view('account.services_preview');
})->name('preview.services');

// Preview route for statistics page (added for testing)
Route::get('/preview/statistics', function () {
    return view('account.statistics_preview');
})->name('preview.statistics');

// Preview route for ratings page (added for testing)
Route::get('/preview/ratings', function () {
    return view('account.ratings_preview');
})->name('preview.ratings');

// Preview route for favorites page (added for testing)
Route::get('/preview/favorites', function () {
    return view('account.favorites_preview');
})->name('preview.favorites');

// Preview route for archive page (added for testing)
Route::get('/preview/archive', function () {
    return view('account.archive_preview');
})->name('preview.archive');

// Dynamic Pages Route (must be last to avoid conflicts)
Route::get('/{slug}', function ($slug) {
    $page = \App\Models\Page::where('slug', $slug)
        ->where('is_published', true)
        ->firstOrFail();
    
    return view('pages.show', compact('page'));
})->name('pages.show');
