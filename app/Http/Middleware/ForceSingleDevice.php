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

        // 1. If not logged in, just continue
        if (!$user) {
            return $next($request);
        }

        // 2. Check if the current session matches the database
        if ($user->active_session_id && $user->active_session_id !== Session::getId()) {
            
            // MISMATCH: Someone else logged in!
            Auth::logout();
            
            // Invalidate this session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'You have been logged out because this account signed in on another device.'
            ]);
        }

        return $next($request);
    }
}