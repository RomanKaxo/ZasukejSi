<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from various sources in order of priority
        $locale = $request->get('locale') // URL parameter
            ?? session()->get('locale') // Session
            ?? $request->cookie('filament_language_switch_locale') // Cookie
            ?? config('app.locale'); // Default

        // Validate and set locale
        if (in_array($locale, ['en', 'cs'])) {
            App::setLocale($locale);
            // Store in session for consistency
            session()->put('locale', $locale);
            
            // Set Carbon locale for date formatting
            Carbon::setLocale($locale);
        }

        return $next($request);
    }
}
