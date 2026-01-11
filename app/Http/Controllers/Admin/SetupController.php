<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Otp\OtpServiceInterface;
use App\Services\Notification\OtpNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class SetupController extends Controller
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
        // Safety: If users exist, abort or redirect (unless in dev cycle)
        if (User::count() > 0) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.setup.wizard');
    }

    public function storeStep1(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'age' => 'required|integer|min:18',
            'birthdate' => 'required|date',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        // Store in Session
        Session::put('setup_data', $data);
        Session::put('setup_step', 2);

        return response()->json(['success' => true]);
    }

    public function sendOtp(Request $request)
    {
        $email = $request->input('email');
        if (!$email) {
            return response()->json(['success' => false, 'message' => 'Email missing']);
        }

        $code = $this->otpService->generate($email, 'setup_verify');
        // We create a dummy user object just for the mailer, or update service to accept email string
        // Custom approach for now since User doesn't exist yet:
        $this->notifier->sendToEmail($email, $code, 'Setup Verification');

        return response()->json(['success' => true]);
    }

    public function verifyAndCreate(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $data = Session::get('setup_data');
        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please restart.']);
        }

        if (!$this->otpService->validate($data['email'], $request->code, 'setup_verify')) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP']);
        }

        // Create Admin User
        $user = User::create([
            'name' => $data['first_name'] . ' ' . $data['last_name'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'middle_name' => $data['middle_name'],
            'age' => $data['age'],
            'birthdate' => $data['birthdate'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(), // Auto-verify
        ]);

        Auth::login($user);
        Session::forget('setup_data');
        Session::forget('setup_step');

        return response()->json(['success' => true, 'redirect' => route('auth.mpin.setup')]);
    }
}
