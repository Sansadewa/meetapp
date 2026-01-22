<?php

namespace App\Http\Middleware;

use Closure;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->is('login'))
        {
            if($request->session()->has('level'))
            {
                return redirect('/');
            }
            return $next($request);
        } else 
        {
            if(!$request->session()->has('level'))
            {
                return redirect('login')->with('message_auth', 'Sesi anda sudah habis. Silahkan login lagi!');
            }
            return $next($request);
        }
        
    }
}
