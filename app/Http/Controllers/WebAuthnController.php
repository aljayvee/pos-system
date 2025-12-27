<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;

class WebAuthnController extends Controller
{
    // 1. REGISTER (Attestation)
    
    /**
     * Return the options to register a new credential.
     */
    public function options(Request $request)
    {
        return $request->user()->makeWebAuthnCredential();
    }

    /**
     * Register the new credential.
     */
    public function register(AttestedRequest $request)
    {
        $request->save();

        return response()->noContent();
    }

    // 2. LOGIN (Assertion)

    /**
     * Return the options to authenticate.
     */
    public function loginOptions(Request $request)
    {
        // Simple login options (user selects credential)
        return \Laragear\WebAuthn\WebAuthnParams::make();
    }

    /**
     * Log the user in.
     */
    public function login(AssertedRequest $request)
    {
        if ($request->login()) {
            $user = Auth::user();
            
            // --- Copy-pasted/Adapted "Smart Check" from AuthController if needed ---
            // For now, basic login is fine. 
            // Ideally, we'd extract that logic to a shared Service too (e.g. SessionService),
            // but for this task, the goal is getting Fingerprint working.
            
            $request->session()->regenerate();

            return response()->json([
                'redirect' => $user->role === 'cashier' ? route('cashier.pos') : route('admin.dashboard')
            ]);
        }

        return response()->json(['message' => 'Authentication failed'], 401);
    }
}
