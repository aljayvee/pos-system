<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Otp\OtpServiceInterface;
use App\Services\Notification\OtpNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    protected $otpService;
    protected $notifier;

    public function __construct(
        OtpServiceInterface $otpService,
        OtpNotificationService $notifier
    ) {
        $this->otpService = $otpService;
        $this->notifier = $notifier;
    }

    // 1. Show Form
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    // 2. Send OTP
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();

        // Generate OTP
        $code = $this->otpService->generate($user->email, 'password_reset');

        // Send Email
        $this->notifier->sendViaEmail($user, $code, 'Reset Password');

        // Store email in session to pass to next step
        return redirect()->route('password.reset.form')->with('email', $request->email);
    }

    // 3. Show Reset Form (OTP Input)
    public function showResetForm(Request $request)
    {
        $email = session('email');
        if (!$email) {
            return redirect()->route('password.request');
        }
        return view('auth.passwords.reset', compact('email'));
    }

    // 4. Process Reset
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|digits:6',
            'password' => 'required|confirmed|min:6', // Strict password rules
        ]);

        // Validate OTP
        if (!$this->otpService->validate($request->email, $request->code, 'password_reset')) {
            return back()->withErrors(['code' => 'Invalid or expired code.']);
        }

        // Update Password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('login')->with('status', 'Password has been reset!');
    }
}
