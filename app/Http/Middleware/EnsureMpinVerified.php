<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureMpinVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If not authenticated, let Auth middleware handle it (or pass through)
        if (!Auth::check()) {
            return $next($request);
        }

        // If session says verified, proceed
        if ($request->session()->get('mpin_verified')) {
            return $next($request);
        }

        // Otherwise, redirect to MPIN login
        return redirect()->route('auth.mpin.login');
    }
}
