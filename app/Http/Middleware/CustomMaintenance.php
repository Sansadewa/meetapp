<?php

namespace App\Http\Middleware;

use Closure;

class CustomMaintenance
{
    public function handle($request, Closure $next)
    {
        // 1. Check if maintenance mode is enabled in .env
        // We use string 'true' check to be safe with .env parsing
        if (env('APP_MAINTENANCE') == 'true' || config('app.maintenance')=='true') {

            // 2. Allow the request if the user has session level 2
            if (session('level') == 2) {
                return $next($request);
            }

            // 3. IMPORTANT: Allow access to the maintenance page itself
            // to prevent a "Too Many Redirects" loop.
            if ($request->is('maintenance')) {
                return $next($request);
            }

            // 4. Optional: Allow login route so you can actually log in to get level 2
            if ($request->is('login') || $request->is('login/*')) {
               return $next($request);
            }

            if ($request->is('logout')) {
                return $next($request);
            }
            // 5. Redirect everyone else to the maintenance page
            return redirect('/maintenance');
        }

        return $next($request);
    }
}