<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache; // Import Cache

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

            // --- NEW LOGIC: CHECK FOR ACTIVE SESSION ---
            if ($user->active_session_id && $user->active_session_id !== $request->session()->getId()) {
                
                // 1. Store this login attempt in Cache for 2 minutes
                // Device 1 will read this to show the popup
                Cache::put('login_request_' . $user->id, [
                    'session_id' => $request->session()->getId(),
                    'ip' => $request->ip(),
                    'device' => $request->header('User-Agent'),
                    'timestamp' => now()
                ], 120); // Expires in 120 seconds

                // 2. Redirect Device 2 to the "Waiting" page
                return redirect()->route('auth.consent.wait');
            }
            // -------------------------------------------

            // Normal Login (First device or Re-login)
            $user->update(['active_session_id' => $request->session()->getId()]);

            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    // Helper for redirection
    protected function redirectBasedOnRole($user)
    {
        return $user->role === 'admin' 
            ? redirect()->route('admin.dashboard') 
            : redirect()->route('cashier.pos');
    }

    // --- NEW: WAITING SCREEN FOR DEVICE 2 ---
    public function showConsentWait()
    {
        return view('auth.consent-wait');
    }

    // --- NEW: DEVICE 2 CHECKS STATUS ---
    public function checkConsentStatus()
    {
        $user = Auth::user();
        
        // If my session is now the Active one, I was approved!
        if ($user->active_session_id === session()->getId()) {
            return response()->json(['status' => 'approved', 'redirect' => $user->role === 'admin' ? route('admin.dashboard') : route('cashier.pos')]);
        }

        // If the cache key is gone and I'm not active, I was denied (or timed out)
        if (!Cache::has('login_request_' . $user->id)) {
            Auth::logout(); // Log me out
            return response()->json(['status' => 'denied']);
        }

        return response()->json(['status' => 'waiting']);
    }

    // --- NEW: DEVICE 1 CHECKS FOR REQUESTS ---
    public function checkLoginRequests()
    {
        $data = Cache::get('login_request_' . Auth::id());
        return response()->json(['has_request' => !!$data, 'details' => $data]);
    }

    // --- NEW: DEVICE 1 DECIDES (YES/NO) ---
    public function resolveLoginRequest(Request $request)
    {
        $user = Auth::user();
        $decision = $request->input('decision'); // 'approve' or 'deny'

        $requestData = Cache::pull('login_request_' . $user->id); // Get and Delete

        if ($decision === 'approve' && $requestData) {
            // Update DB to point to Device 2
            $user->update(['active_session_id' => $requestData['session_id']]);
            
            // Device 1 (Current) is now invalid. 
            // The Middleware will kick Device 1 out on the NEXT request.
            return response()->json(['success' => true, 'action' => 'logout_self']);
        }

        // If Denied, we just cleared the cache. Device 2 will see "denied" on next poll.
        return response()->json(['success' => true, 'action' => 'stay']);
    }

    public function logout(Request $request)
    {
        Auth::user()->update(['active_session_id' => null]); // Clear DB on clean logout
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}