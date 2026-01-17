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
    public function options(\Laragear\WebAuthn\Http\Requests\AttestationRequest $request)
    {
        // Generates creation options for the current user
        return $request->toCreate();
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
    public function loginOptions(\Laragear\WebAuthn\Http\Requests\AssertionRequest $request)
    {
        // Generates assertion options (allows "userless" / passkey flow if email is empty)
        return $request->toVerify($request->validate(['email' => 'sometimes|email|string']));
    }

    /**
     * Log the user in.
     */
    public function login(AssertedRequest $request)
    {
        if ($request->login()) {
            $user = Auth::user();
            $request->session()->regenerate();

            return response()->json([
                'redirect' => $user->role === 'cashier' ? route('cashier.pos') : route('admin.dashboard')
            ]);
        }

        return response()->json(['message' => 'Authentication failed'], 401);
    }
}
