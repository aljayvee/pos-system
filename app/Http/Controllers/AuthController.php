<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 1. Show the Login Form (This was missing!)
    public function showLogin()
    {
        return view('auth.login');
    }

    // 2. Handle the Login Logic
    // In app/Http/Controllers/AuthController.php

public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    if (Auth::attempt($credentials)) {
        // 1. Regenerate the session first (Security best practice)
        $request->session()->regenerate();

        // 2. MANUALLY update the user's session ID here (Fixes the bug)
        // This ensures the DB gets the *new* ID, not the old one.
        $user = Auth::user();
        $user->update([
            'active_session_id' => $request->session()->getId()
        ]);

        // Redirect based on role
        $role = $user->role;
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        
        return redirect()->route('cashier.pos');
    }

    return back()->withErrors(['email' => 'Invalid credentials.']);
}

    // 3. Handle Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}