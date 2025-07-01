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

        // Als 2FA actief is, maar nog niet geverifieerd, en je zit NIET op /verify
        if (
            $user &&
            $user->needsTwoFactorVerification() &&
            !$request->is('two-factor/verify') &&
            !$request->is('two-factor/verify/*')
        ) {
            return redirect()->route('two-factor.verify');
        }

        return $next($request);
    }
}
