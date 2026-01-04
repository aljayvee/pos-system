<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemSetup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce for authenticated Admins
        if (auth()->check() && auth()->user()->role === 'admin') {

            // Allow if seeking setup routes or logout
            if ($request->routeIs('admin.setup.*') || $request->routeIs('logout')) {
                return $next($request);
            }

            // Check if setup is complete
            $setupComplete = \App\Models\Setting::where('key', 'setup_complete')->value('value');

            if (!$setupComplete || $setupComplete == '0') {
                return redirect()->route('admin.setup.index');
            }
        }

        return $next($request);
    }
}
