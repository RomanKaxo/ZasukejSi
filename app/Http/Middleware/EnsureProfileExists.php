<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileExists
{
    /**
     * Ensure the authenticated user has a profile before accessing profile-dependent sections.
     * Redirects to the dashboard (profile creation page) if no profile exists.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admins can access everything
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Check if user has a profile
        if (!$user->profile) {
            return redirect()
                ->route('account.dashboard')
                ->with('profile_required', __('front.account.profile_required'));
        }

        return $next($request);
    }
}
