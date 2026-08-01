<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActivity
{
    /** Only write once per this many seconds, per user. */
    private const THROTTLE_SECONDS = 60;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $now = time();

            if ($now - (int) $user->last_activity > self::THROTTLE_SECONDS) {
                // Write straight through the query builder instead of saving the model.
                //
                // The previous implementation set `$user->timestamps = false` before
                // saving so `updated_at` would not move. That flag also removes
                // created_at/updated_at from Model::getDates(), so for the rest of the
                // request those attributes were plain strings instead of Carbon
                // instances — any view calling `$user->created_at->format(...)` or
                // `->diffForHumans()` then fataled with
                // "Call to a member function diffForHumans() on string".
                // Because the write is throttled to once a minute, the breakage only
                // showed up on the first request after an idle period, which made it
                // look intermittent.
                DB::table($user->getTable())
                    ->where($user->getKeyName(), $user->getKey())
                    ->update(['last_activity' => $now]);

                // Keep the in-memory model in sync without marking it dirty.
                $user->setAttribute('last_activity', $now);
                $user->syncOriginalAttribute('last_activity');
            }
        }

        return $next($request);
    }
}
