<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Authenticatable;
use Laragear\WebAuthn\WebAuthnParams;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;

class WebAuthnService
{
    /**
     * Generate options for registering a new credential (attestation).
     */
    public function generateRegisterOptions(Authenticatable $user)
    {
        // This is handled by the package's helper/response usually, 
        // but we can wrap business logic here if needed.
        // For Laragear, it's often done via User model methods.
        return $user->makeWebAuthnClaims();
    }

    /**
     * Generate options for logging in (assertion).
     */
    public function generateLoginOptions(Request $request)
    {
        // If we know the user (e.g. from email input), we can narrow it down.
        // If not, we do a "passkey" login where the user selects the account.
        // For now, let's assume we might want to support both.
        return WebAuthnParams::make();
    }
}
