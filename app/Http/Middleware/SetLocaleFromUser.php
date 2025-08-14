<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;


/**
 * Middleware to set the application locale based on the authenticated user's preference.
 */
class SetLocaleFromUser
{
    public function handle($request, Closure $next)
    {
        if ($request->user() && $request->user()->locale) {
            App::setLocale($request->user()->locale);
        }
        return $next($request);
    }
}
