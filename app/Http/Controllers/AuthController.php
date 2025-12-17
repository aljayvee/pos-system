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

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
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

                // Send to Waiting Room
                return redirect()->route('auth.consent.wait', ['request_id' => $requestId]);
            }
            // --- SMART CHECK END ---

            // Normal Login (First time or clean logout)
            $request->session()->regenerate();
            $this->updateUserSession($user, $request);

            return $this->redirectBasedOnRole($user);
        }

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
        return $user->role === 'admin' 
            ? redirect()->route('admin.dashboard') 
            : redirect()->route('cashier.pos');
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
        } 
        else {
            $this->removeRequest($user->id, $requestId);
            return response()->json(['success' => true, 'action' => 'stay']);
        }
    }

    protected function removeRequest($userId, $requestId)
    {
        $pending = Cache::get('login_requests_' . $userId, []);
        if(isset($pending[$requestId])) {
            unset($pending[$requestId]);
            Cache::put('login_requests_' . $userId, $pending, 300);
        }
    }

    public function logout(Request $request)
    {
        if(Auth::check()){
             Auth::user()->update(['active_session_id' => null]);
             Cache::forget('user_device_' . Auth::id()); // Clear device memory on clean logout
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}