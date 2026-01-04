<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Rate Limiting (Throttle Login Attempts)
        $key = 'login_attempts:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['email' => 'Too many login attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.']);
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Check if user is active (Approval Workflow)
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account is currently inactive or pending approval.']);
            }

            // --- SMART CHECK START ---

            // Check if there is an existing session in the DB
            if ($user->active_session_id && $user->active_session_id !== Session::getId()) {

                // 1. GET LAST KNOWN DEVICE (From Cache)
                $lastDevice = Cache::get('user_device_' . $user->id);
                $currentDevice = $request->header('User-Agent');

                // 2. AUTO-APPROVE if it's the SAME DEVICE (Browser/OS)
                // This fixes the "Closed Tab" or "Session Timeout" issue.
                // If you are on the exact same browser that was logged in before, we let you back in.
                if ($lastDevice === $currentDevice) {
                    $request->session()->regenerate();
                    $this->updateUserSession($user, $request);
                    return $this->redirectBasedOnRole($user);
                }

                // 3. IF DIFFERENT DEVICE -> TRIGGER CONSENT FLOW

                // (Rate Limit to prevent spam)
                $key = 'consent_request:' . $user->id;
                if (RateLimiter::tooManyAttempts($key, 3)) {
                    Auth::logout();
                    return back()->withErrors(['email' => 'Too many login attempts. Please check your email.']);
                }
                RateLimiter::hit($key, 60);

                // Generate Ticket
                $requestId = (string) Str::uuid();
                $requestData = [
                    'request_id' => $requestId,
                    'session_id' => Session::getId(),
                    'ip' => $request->ip(),
                    'device' => $request->header('User-Agent'), // Storing device name for the popup
                    'timestamp' => now()->toDateTimeString()
                ];

                // Save Ticket
                $pendingRequests = Cache::get('login_requests_' . $user->id, []);
                $pendingRequests[$requestId] = $requestData;
                Cache::put('login_requests_' . $user->id, $pendingRequests, 300);

                // Broadcast Event
                \App\Events\LoginRequestCreated::dispatch($requestData);

                // Send to Waiting Room
                return redirect()->route('auth.consent.wait', ['request_id' => $requestId]);
            }
            // --- SMART CHECK END ---

            // Normal Login (First time or clean logout)
            $request->session()->regenerate();
            $this->updateUserSession($user, $request);

            // Clear Rate Limiter on Success
            RateLimiter::clear($key);

            return $this->redirectBasedOnRole($user);
        }

        // Increment Rate Limiter on Failure
        RateLimiter::hit($key, 60);

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    // Helper to update DB and Cache Device Info
    protected function updateUserSession($user, Request $request)
    {
        $sessionId = $request->session()->getId();

        // Update DB with new Session ID
        $user->update(['active_session_id' => $sessionId]);

        // Update Device Cache (We now track User-Agent instead of IP)
        Cache::put('user_device_' . $user->id, $request->header('User-Agent'), 86400); // Store for 24 hours
    }

    protected function redirectBasedOnRole($user)
    {
        // Cashiers go directly to POS
        if ($user->role === 'cashier') {
            return redirect()->route('cashier.pos');
        }

        // Everyone else (Admin, Manager, Supervisor, Stock Clerk, Auditor) goes to Dashboard
        return redirect()->route('admin.dashboard');
    }

    // --- WAITING ROOM LOGIC ---

    public function showConsentWait(Request $request)
    {
        return view('auth.consent-wait', ['request_id' => $request->query('request_id')]);
    }

    public function checkConsentStatus(Request $request)
    {
        $user = Auth::user();

        // Approved? (DB matches my browser session)
        if ($user->active_session_id === Session::getId()) {
            $requestId = $request->query('request_id');
            $this->removeRequest($user->id, $requestId);
            return response()->json(['status' => 'approved', 'redirect' => $user->role === 'admin' ? route('admin.dashboard') : route('cashier.pos')]);
        }

        // Denied? (Ticket gone)
        $requestId = $request->query('request_id');
        $pending = Cache::get('login_requests_' . $user->id, []);

        if (!isset($pending[$requestId])) {
            Auth::logout();
            return response()->json(['status' => 'denied']);
        }

        return response()->json(['status' => 'waiting']);
    }

    public function checkLoginRequests()
    {
        $pending = Cache::get('login_requests_' . Auth::id(), []);
        $firstRequest = empty($pending) ? null : reset($pending);
        return response()->json(['has_request' => !empty($pending), 'details' => $firstRequest]);
    }

    public function resolveLoginRequest(Request $request)
    {
        $user = Auth::user();
        $decision = $request->input('decision');
        $requestId = $request->input('request_id');

        $pending = Cache::get('login_requests_' . $user->id, []);
        if (!isset($pending[$requestId])) {
            return response()->json(['success' => false, 'message' => 'Request expired']);
        }

        if ($decision === 'approve') {
            $requestData = $pending[$requestId];

            // Approve: Update DB to the NEW session
            $user->update(['active_session_id' => $requestData['session_id']]);

            // Update Known Device to the NEW Device (Critical step!)
            Cache::put('user_device_' . $user->id, $requestData['device'], 86400);

            Cache::forget('login_requests_' . $user->id);
            return response()->json(['success' => true, 'action' => 'logout_self']);
        } else {
            $this->removeRequest($user->id, $requestId);
            return response()->json(['success' => true, 'action' => 'stay']);
        }
    }

    protected function removeRequest($userId, $requestId)
    {
        $pending = Cache::get('login_requests_' . $userId, []);
        if (isset($pending[$requestId])) {
            unset($pending[$requestId]);
            Cache::put('login_requests_' . $userId, $pending, 300);
        }
    }

    // --- FORCE LOGIN (EMAIL LINK) ---
    public function verifyForceLogin(Request $request, $id)
    {
        // 1. Find User (Middleware 'signed' already verified the signature)
        $user = \App\Models\User::findOrFail($id);

        // 2. Login User
        Auth::login($user);
        $request->session()->regenerate();

        // 3. Trust this device (Update Session & Cache)
        $this->updateUserSession($user, $request);

        // 4. Clear any pending login requests for this user (cleanup)
        Cache::forget('login_requests_' . $user->id);

        // 5. Redirect
        return $this->redirectBasedOnRole($user);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::user()->update(['active_session_id' => null]);
            Cache::forget('user_device_' . Auth::id()); // Clear device memory on clean logout
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}