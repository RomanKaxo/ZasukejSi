<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserGender
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $requiredGender  'male' or 'female'
     */
    public function handle(Request $request, Closure $next, string $requiredGender): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admins can access everything
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Check if user has the required gender
        if ($user->gender !== $requiredGender) {
            // Only confirmed male users are sent to the member dashboard — that
            // route is itself guarded by `gender:male`, so redirecting a female
            // or gender-less account there would bounce forever. Everyone else
            // goes to /account, which carries no gender guard of its own.
            if ($user->isMale()) {
                return redirect()->route('account.member.dashboard');
            }

            return redirect()->route('account.dashboard');
        }

        return $next($request);
    }
}
