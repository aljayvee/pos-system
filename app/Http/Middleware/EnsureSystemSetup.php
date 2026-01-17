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
        \Illuminate\Support\Facades\Log::info('EnsureSystemSetup: Start ' . $request->path());
        $start = microtime(true);
        // Cache the user count check to avoid DB query on every request (Optimization)
        $hasUsers = \Illuminate\Support\Facades\Cache::remember('system_has_users', 60, function () {
            return User::exists(); // exists() is faster than count() if we just care about > 0
        });

        if (!$hasUsers) {
            if ($request->is('setup*')) {
                return $next($request);
            }

            if (
                $request->is('css/*') ||
                $request->is('js/*') ||
                $request->is('build/*') ||
                $request->is('fonts/*') ||
                $request->is('images/*') ||
                $request->is('favicon.ico') ||
                str_contains($request->path(), '.')
            ) {
                return $next($request);
            }

            return redirect()->route('admin.setup.index');
        }

        \Illuminate\Support\Facades\Log::info('EnsureSystemSetup: End ' . $request->path() . ' Duration: ' . (microtime(true) - $start));
        return $next($request);
    }
}
