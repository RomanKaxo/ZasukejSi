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

        // Administrátor tudy výjimku nemá.
        //
        // Míval ji, a znamenala to, že administrátor-muž měl ve svém účtu
        // ženské stránky: fotky inzerátu, služby, ceny, statistiky. Vypadalo
        // to jako chyba, protože to chyba byla — účet je jeho vlastní, ne
        // nástroj správy. Cizí profily se spravují v administraci, kde je na
        // to editace i log úprav.
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
