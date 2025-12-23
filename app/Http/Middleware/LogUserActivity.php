<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ignore debugbar, assets, etc.
        if ($request->is('_debugbar/*', 'js/*', 'css/*', 'images/*', 'fonts/*')) {
            return $response;
        }

        // Only log authenticated users? Or everything?
        // User said "Every click".
        // Use try-catch to avoid breaking app if logging fails
        try {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $user = \Illuminate\Support\Facades\Auth::user();
                $method = $request->method();
                $url = $request->path();
                $ip = $request->ip();
                
                // Determine readable action
                $action = $method . ' ' . $url;
                if ($request->route()) {
                    $action = $method . ' ' . ($request->route()->getName() ?? $url);
                }

                \App\Models\ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'Navigation', // Grouping category
                    'description' => "User accessed: {$method} /{$url} (IP: {$ip})",
                ]);
            }
        } catch (\Exception $e) {
            // Fail silently to prevent blocking user
            // Log::error($e->getMessage()); 
        }

        return $response;
    }
}
