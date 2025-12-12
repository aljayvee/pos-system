<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        

        // Check if user's role is in the allowed list passed from route
        if (!in_array($user->role, $roles)) {
            abort(403, 'Unauthorized access.');
        }


        return $next($request);
    }
}