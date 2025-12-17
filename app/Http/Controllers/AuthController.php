<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter; // For Throttling
use Illuminate\Support\Facades\Mail; // For Email
use Illuminate\Support\Str; // For UUID
use App\Mail\ForceLoginRequest;

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
            $request->session()->regenerate();
            $user = Auth::user();

            // --- CHECK FOR ACTIVE SESSION ---
            if ($user->active_session_id && $user->active_session_id !== $request->session()->getId()) {
                
                // 1. THROTTLE CHECK (Safeguard #2)
                $key = 'consent_request:' . $user->id;
                if (RateLimiter::tooManyAttempts($key, 3)) { // 3 attempts per minute
                    Auth::logout();
                    return back()->withErrors(['email' => 'Too many login attempts. Please check your email or try again later.']);
                }
                RateLimiter::hit($key, 60); // Decay seconds = 60

                // 2. GENERATE UNIQUE REQUEST ID (Safeguard #3)
                $requestId = (string) Str::uuid();
                
                $requestData = [
                    'request_id' => $requestId,
                    'session_id' => $request->session()->getId(),
                    'ip' => $request->ip(),
                    'device' => $request->header('User-Agent'),
                    'timestamp' => now()->toDateTimeString()
                ];

                // 3. APPEND TO REQUEST LIST (Fixes "Overwrite" bug)
                $pendingRequests = Cache::get('login_requests_' . $user->id, []);
                $pendingRequests[$requestId] = $requestData;
                Cache::put('login_requests_' . $user->id, $pendingRequests, 300); // 5 Minutes

                // 4. Redirect with Request ID
                return redirect()->route('auth.consent.wait', ['request_id' => $requestId]);
            }

            // Normal Login
            $user->update(['active_session_id' => $request->session()->getId()]);
            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    protected function redirectBasedOnRole($user)
    {
        return $user->role === 'admin' 
            ? redirect()->route('admin.dashboard') 
            : redirect()->route('cashier.pos');
    }

    // --- WAITING SCREEN ---
    public function showConsentWait(Request $request)
    {
        return view('auth.consent-wait', ['request_id' => $request->query('request_id')]);
    }

    // --- DEVICE 2 CHECKS STATUS ---
    public function checkConsentStatus(Request $request)
    {
        $user = Auth::user();
        
        // 1. Am I approved? (DB Match)
        if ($user->active_session_id === session()->getId()) {
             // Clear my request from cache to clean up
             $requestId = $request->query('request_id');
             $pending = Cache::get('login_requests_' . $user->id, []);
             if(isset($pending[$requestId])) {
                 unset($pending[$requestId]);
                 Cache::put('login_requests_' . $user->id, $pending, 300);
             }
             
             return response()->json(['status' => 'approved', 'redirect' => $user->role === 'admin' ? route('admin.dashboard') : route('cashier.pos')]);
        }

        // 2. Am I denied? (Check if my Request ID still exists in Cache)
        $requestId = $request->query('request_id');
        $pending = Cache::get('login_requests_' . $user->id, []);

        if (!isset($pending[$requestId])) {
            Auth::logout();
            return response()->json(['status' => 'denied']);
        }

        return response()->json(['status' => 'waiting']);
    }

    // --- DEVICE 1 CHECKS FOR REQUESTS ---
    public function checkLoginRequests()
    {
        $pending = Cache::get('login_requests_' . Auth::id(), []);
        
        // Return the first one in the queue
        $firstRequest = empty($pending) ? null : reset($pending);
        
        return response()->json([
            'has_request' => !empty($pending), 
            'count' => count($pending),
            'details' => $firstRequest // Front-end will only show one at a time for simplicity
        ]);
    }

    // --- DEVICE 1 DECIDES ---
    public function resolveLoginRequest(Request $request)
    {
        $user = Auth::user();
        $decision = $request->input('decision'); 
        $requestId = $request->input('request_id');

        $pending = Cache::get('login_requests_' . $user->id, []);

        if (!isset($pending[$requestId])) {
            return response()->json(['success' => false, 'message' => 'Request expired or processed']);
        }

        if ($decision === 'approve') {
            $requestData = $pending[$requestId];
            
            // Set DB to new session
            $user->update(['active_session_id' => $requestData['session_id']]);
            
            // Clear ALL pending requests (Optional: or just this one)
            Cache::forget('login_requests_' . $user->id);

            return response()->json(['success' => true, 'action' => 'logout_self']);
        } 
        else {
            // Deny: Just remove this specific request from array
            unset($pending[$requestId]);
            Cache::put('login_requests_' . $user->id, $pending, 300);
            
            return response()->json(['success' => true, 'action' => 'stay']);
        }
    }

    // --- SAFEGUARD #1: FORCE LOGIN (SEND EMAIL) ---
    public function sendForceLoginEmail(Request $request)
    {
        $user = Auth::user();
        $requestId = $request->input('request_id');
        $pending = Cache::get('login_requests_' . $user->id, []);
        
        // Use request details if available, or current request info
        $details = $pending[$requestId] ?? [
            'device' => $request->header('User-Agent'),
            'ip' => $request->ip()
        ];

        Mail::to($user->email)->send(new ForceLoginRequest($user, $details));

        return response()->json(['success' => true, 'message' => 'Email sent! Check your inbox.']);
    }

    // --- SAFEGUARD #1: VERIFY MAGIC LINK ---
    public function verifyForceLogin(Request $request, $id)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or Expired Link');
        }

        $user = \App\Models\User::findOrFail($id);
        
        // 1. Login the user (Session might be different from the browser doing the verifying)
        Auth::login($user);
        $request->session()->regenerate();

        // 2. Force update DB (Kicks everyone else out)
        $user->update(['active_session_id' => $request->session()->getId()]);

        // 3. Clear pending requests
        Cache::forget('login_requests_' . $user->id);

        return $this->redirectBasedOnRole($user);
    }

    public function logout(Request $request)
    {
        if(Auth::check()){
             Auth::user()->update(['active_session_id' => null]);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}