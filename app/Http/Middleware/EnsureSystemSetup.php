<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class EnsureSystemSetup
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If system is empty (no users), enforce setup
        // But allow setup routes to pass through to avoid infinite loops
        if (User::count() === 0) {
            if (!$request->is('setup*') && !$request->is('css/*') && !$request->is('js/*')) {
                return redirect()->route('setup.index');
            }
        }

        return $next($request);
    }
}
