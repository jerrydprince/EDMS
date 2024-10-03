<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class updateOnlineStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       // return $next($request);
       $response = $next($request);

       if (Auth::check()) {
           Auth::user()->is_online = 1; // Keep user online while session is active
           Auth::user()->save();
       }

       return $response;
    }

    public function terminate($request, $response)
    {
        if (Auth::check()) {
            Auth::user()->is_online = 0; 
            Auth::user()->save();
        }
    }
}
