<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Otp\OtpServiceInterface;
use App\Services\Notification\OtpNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class OnboardingController extends Controller
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

    public function index()
    {
        $user = Auth::user();
        if ($user->email_verified_at) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.onboarding.index', ['user' => $user]);
    }

    public function sendOtp(Request $request)
    {
        $email = $request->input('email');
        if (!$email) {
            return response()->json(['success' => false, 'message' => 'Email missing']);
        }

        // Generate OTP
        $code = $this->otpService->generate($email, 'onboarding_verify');

        // Send Email
        $this->notifier->sendToEmail($email, $code, 'Account Verification');

        return response()->json(['success' => true]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6'
        ]);

        $user = Auth::user();

        // Verify OTP
        if (!$this->otpService->validate($request->email, $request->code, 'onboarding_verify')) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP']);
        }

        // Update User
        $user->email = $request->email;
        $user->email_verified_at = now();
        $user->save();

        // Set Flag for Welcome Screen
        Session::put('just_onboarded', true);

        // Redirect to MPIN Setup
        return response()->json(['success' => true, 'redirect' => route('auth.mpin.setup')]);
    }

    public function welcome()
    {
        return view('admin.onboarding.welcome');
    }
}
