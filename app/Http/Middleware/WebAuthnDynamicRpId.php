<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebAuthnDynamicRpId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Fix for WebAuthn RP ID mismatch (Invalid Domain Error)
        // Detects if we are running on localhost or 127.0.0.1 and adjusts config dynamically.
        $host = $request->getHost();

        if (in_array($host, ['localhost', '127.0.0.1'])) {
            config(['webauthn.relying_party.id' => $host]);
        }

        return $next($request);
    }
}
