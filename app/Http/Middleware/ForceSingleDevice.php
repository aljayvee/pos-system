<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ForceSingleDevice
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // 1. If not logged in, continue
        if (!$user) {
            return $next($request);
        }

        // 2. EXCEPTION: Allow the "Waiting for Consent" page to load
        if ($request->routeIs('auth.consent.*')) {
            return $next($request);
        }

        // 3. Check for Session Mismatch
        if ($user->active_session_id && $user->active_session_id !== Session::getId()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'You have been logged out because this account signed in on another device.'
            ]);
        }

        return $next($request);
    }
}