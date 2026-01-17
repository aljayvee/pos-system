<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\MpinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MpinController extends Controller
{
    protected $mpinService;
    protected $otpService;
    protected $notifier;

    public function __construct(
        MpinService $mpinService,
        \App\Services\Otp\OtpServiceInterface $otpService,
        \App\Services\Notification\OtpNotificationService $notifier
    ) {
        $this->mpinService = $mpinService;
        $this->otpService = $otpService;
        $this->notifier = $notifier;
    }

    /**
     * Show the MPIN entry form (or setup if missing).
     */
    public function showMpinForm()
    {
        $user = Auth::user();

        // If user has no MPIN, redirect to setup
        if (!$this->mpinService->hasMpin($user)) {
            return redirect()->route('auth.mpin.setup');
        }

        return view('auth.mpin-login');
    }

    /**
     * Verify the entered MPIN.
     */
    /**
     * Verify the entered MPIN.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'mpin' => 'required|digits_between:7,16',
        ]);

        $user = Auth::user();
        $key = 'mpin_attempts:' . $user->id;

        // Check for too many attempts (Max 5)
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);
            return back()
                ->withErrors(['mpin' => "Too many incorrect attempts. Please try again in {$minutes} minutes."])
                ->with('retry_after', $seconds);
        }

        if ($this->mpinService->verifyMpin($user, $request->mpin)) {
            // Success: Clear attempts
            \Illuminate\Support\Facades\RateLimiter::clear($key);

            // Set session flag to indicate MPIN verified
            $request->session()->put('mpin_verified', true);
            $request->session()->put('mpin_verified_at', now());

            return $this->redirectBasedOnRole($user);
        }

        // Failure: Increment attempts with dynamic decay (Exponential-ish Backoff)
        // If attempts are piling up, increase the penalty time for this new hit.
        $attempts = \Illuminate\Support\Facades\RateLimiter::attempts($key) + 1;

        $decaySeconds = match (true) {
            $attempts >= 20 => 1200, // 20 minutes if persistant
            $attempts >= 10 => 900, // 15 minutes if persistant
            $attempts >= 5 => 300,  // 5 minutes lockout
            default => 60,          // 1 minute default
        };

        \Illuminate\Support\Facades\RateLimiter::hit($key, $decaySeconds);

        $remaining = 5 - $attempts;
        $msg = 'Invalid MPIN.';
        if ($remaining > 0) {
            $msg .= " You have {$remaining} attempts remaining.";
            return back()->withErrors(['mpin' => $msg]);
        } else {
            $msg .= " You are locked out.";
            // Immediately return lockout time if they just hit the limit
            $seconds = $decaySeconds;
            return back()
                ->withErrors(['mpin' => $msg])
                ->with('retry_after', $seconds);
        }
    }

    /**
     * Show the MPIN setup form.
     */
    public function showSetupForm()
    {
        $user = Auth::user();

        // If already set, maybe redirect to login? Or allow reset here? 
        // For now, if set, go to login.
        if ($this->mpinService->hasMpin($user)) {
            return redirect()->route('auth.mpin.login');
        }

        return view('auth.mpin-setup');
    }

    /**
     * Store the new MPIN.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mpin' => 'required|digits_between:7,16|confirmed',
        ]);

        $this->mpinService->setMpin(Auth::user(), $request->mpin);

        // Auto-verify after setup
        $request->session()->put('mpin_verified', true);
        $request->session()->put('mpin_verified_at', now());

        // Check if this is the initial setup flow
        if ($request->session()->pull('from_setup_wizard')) {
            \Illuminate\Support\Facades\Mail::to(Auth::user())->send(new \App\Mail\WelcomeMail(Auth::user()));
        }

        // Check if this is the New User Onboarding flow
        if ($request->session()->pull('just_onboarded')) {
            return redirect()->route('onboarding.welcome');
        }

        return $this->redirectBasedOnRole(Auth::user())->with('success', 'MPIN set successfully!');
    }

    /**
     * Show Forgot MPIN form (Password verification).
     */
    public function showForgotForm()
    {
        return view('auth.mpin-forgot');
    }

    /**
     * Verify password and allow reset.
     */
    // --- OTP Reset Flow ---

    public function sendResetOtp()
    {
        $user = Auth::user();
        $code = $this->otpService->generate($user->email, 'mpin_reset');
        $this->notifier->sendViaEmail($user, $code, 'Reset MPIN');

        return response()->json(['success' => true, 'message' => 'OTP sent to ' . $user->email]);
    }

    /**
     * Verify both Password AND OTP, then allow MPIN reset.
     */
    /**
     * Step 1: Verify Credentials (Email, Password, OTP).
     */
    public function verifyResetCredentials(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'otp' => 'required|digits:6',
        ]);

        $user = Auth::user();

        // 1. Email Check
        if (strtolower($request->email) !== strtolower($user->email)) {
            return back()->withErrors(['email' => 'Email address does not match your account.']);
        }

        // 2. Password Check
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        // 3. OTP Check
        if (!$this->otpService->validate($user->email, $request->otp, 'mpin_reset')) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        // Success: Set temporary session flag to allow access to reset step
        session(['mpin_reset_authorized' => true]);

        return redirect()->route('auth.mpin.reset.form');
    }

    /**
     * Step 2: Show MPIN Reset Form.
     */
    public function showResetMpinForm()
    {
        if (!session('mpin_reset_authorized')) {
            return redirect()->route('auth.mpin.forgot')->withErrors(['otp' => 'Session expired. Please verify again.']);
        }

        return view('auth.mpin-reset-password'); // New view name matching user request pattern
    }

    /**
     * Step 2 Action: Set New MPIN.
     */
    public function resetMpin(Request $request)
    {
        if (!session('mpin_reset_authorized')) {
            return redirect()->route('auth.mpin.forgot');
        }

        $request->validate([
            'mpin' => 'required|digits_between:7,16|confirmed',
        ]);

        $this->mpinService->setMpin(Auth::user(), $request->mpin);

        // Clear reset flag, set verified flag
        session()->forget('mpin_reset_authorized');
        session(['mpin_verified' => true]);

        return $this->redirectBasedOnRole(Auth::user())->with('success', 'MPIN changed successfully.');
    }

    protected function redirectBasedOnRole($user)
    {
        if ($user->role === 'cashier') {
            return redirect()->route('cashier.pos');
        }
        return redirect()->route('admin.dashboard');
    }
}
