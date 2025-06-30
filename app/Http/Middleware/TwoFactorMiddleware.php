<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user) {
            // Als gebruiker geen 2FA heeft ingesteld, dwing setup af
            if (!$user->google2fa_enabled) {
                // Alleen toestaan om naar setup te gaan, niet naar andere pagina's
                if (!$request->routeIs('two-factor.setup') && !$request->routeIs('two-factor.enable') && !$request->routeIs('logout')) {
                    return redirect()->route('two-factor.setup');
                }
            }
            // Als 2FA is ingesteld maar verificatie nodig is
            elseif ($user->needsTwoFactorVerification()) {
                // Alleen toestaan om naar verify te gaan, niet naar andere pagina's (inclusief setup)
                if (!$request->routeIs('two-factor.verify') && !$request->routeIs('two-factor.confirm') && !$request->routeIs('logout')) {
                    return redirect()->route('two-factor.verify');
                }
            }
        }

        return $next($request);
    }
}
