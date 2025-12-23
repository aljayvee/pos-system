<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$requirements)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        
        // If no requirements passed, allow (default behavior, though atypical)
        if (empty($requirements)) {
            return $next($request);
        }

        // DEBUG ROLE MIDDLEWARE
        // \Illuminate\Support\Facades\Log::info("RoleMiddleware Check. User: {$user->role} Requirements: " . implode(',', $requirements));

        foreach ($requirements as $requirement) {
            // Trim requirement in case of spaces
            $requirement = trim($requirement);
            
            // 1. Check if it matches the Users role directly (Legacy Support)
            if ($user->role === $requirement) {
                return $next($request);
            }

            // 2. Check if it's a Permission
            if ($user->hasPermission($requirement)) {
                return $next($request);
            }
        }

        // If generic error page exists, use it, otherwise 403
        abort(403, 'Unauthorized access.');
    }
}