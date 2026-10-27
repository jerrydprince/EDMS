<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class updateOnlineStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user()) {
            $user = $request->user();
            // Cache active status for 3 minutes (refreshed on every request)
            Cache::put('user-is-online-' . $user->id, true, now()->addMinutes(3));

            if ($user->getRawOriginal('is_online') != 1) {
                \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', $user->id)
                    ->update(['is_online' => 1]);
            }
        }

        return $response;
    }

    // public function terminate($request, $response)
    // {
    //     if (Auth::check()) {
    //         Auth::user()->is_online = 0; 
    //         Auth::user()->save();
    //     }
    // }
}
