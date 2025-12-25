<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\CashRegisterSession;
use Illuminate\Support\Facades\Auth;

class EnsureRegisterOpen
{
    public function handle(Request $request, Closure $next)
    {
        $storeId = session('active_store_id', Auth::user()->store_id ?? 1);

        $isOpen = CashRegisterSession::where('store_id', $storeId)
            ->where('status', 'open')
            ->exists();

        // Allow 'open' route to pass through obviously
        if ($request->is('cashier/register/open') || $request->is('cashier/register/status')) {
            return $next($request);
        }

        if (!$isOpen) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Register Closed', 'code' => 'REGISTER_CLOSED'], 403);
            }
            // For web requests, maybe redirect to a "Closed" page or let JS handle it
            return redirect()->route('cashier.pos')->with('error', 'Register is Closed. Please open it first.');
        }

        return $next($request);
    }
}
