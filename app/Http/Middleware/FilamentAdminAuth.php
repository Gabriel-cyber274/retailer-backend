<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class FilamentAdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('filament')->check()) {
            $user = Auth::guard('filament')->user();
            if (! $user || ! $user->admin) {
                Auth::guard('filament')->logout();
                abort(403, 'Unauthorized.');
            }
            return $next($request);
        }

        return $next($request);
    }
}
