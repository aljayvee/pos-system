<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Otp\OtpServiceInterface;
use App\Services\Notification\OtpNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

    // --- VIEW: Wizard Entry Point ---
    public function showLinkRequestForm()
    {
        return view('auth.passwords.wizard');
    }

    // --- API: Step 1 - Search Username ---
    public function search(Request $request)
    {
        $request->validate(['username' => 'required|string']);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return response()->json([
                'found' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Mask Email (e.g., j******@gmail.com)
        $email = $user->email;
        $parts = explode('@', $email);
        $maskedEmail = substr($parts[0], 0, 1) . str_repeat('*', max(strlen($parts[0]) - 1, 5)) . '@' . $parts[1];

        return response()->json([
            'found' => true,
            'user' => [
                'first_name' => $user->first_name ?? 'Unknown', // Ensure these columns exist
                'last_name' => $user->last_name ?? 'User',
                'masked_email' => $maskedEmail
            ]
        ]);
    }

    // --- API: Step 2 - Send OTP ---
    public function sendOtp(Request $request)
    {
        $request->validate(['username' => 'required|string|exists:users,username']);

        $user = User::where('username', $request->username)->first();

        if (!$user->email) {
            return response()->json(['success' => false, 'message' => 'No email linked to this account.'], 400);
        }

        // Generate OTP
        $code = $this->otpService->generate($user->email, 'password_reset');

        // Send Email
        $this->notifier->sendViaEmail($user, $code, 'Reset Password');

        return response()->json(['success' => true, 'message' => 'OTP sent successfully']);
    }

    // --- API: Step 3 - Verify OTP ---
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'username' => 'required|string|exists:users,username',
            'code' => 'required|digits:6'
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$this->otpService->validate($user->email, $request->code, 'password_reset')) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired code.'], 422);
        }

        // Generate a temporary signature/token for the final reset step
        // This prevents users from skipping the OTP step
        $token = hash_hmac('sha256', $user->id . Str::random(10) . now(), config('app.key'));

        // Store token in cache/session for validation in the next step (Short lived: 10 mins)
        // For simplicity, we can use the cache with the username as key prefix
        \Illuminate\Support\Facades\Cache::put('reset_token_' . $user->username, $token, 600);

        return response()->json(['success' => true, 'token' => $token]);
    }

    // --- API: Step 4 - Reset Password ---
    public function resetWizard(Request $request)
    {
        $request->validate([
            'username' => 'required|string|exists:users,username',
            'token' => 'required|string',
            'password' => 'required|confirmed|min:6',
        ]);

        // Validate Token
        $cachedToken = \Illuminate\Support\Facades\Cache::get('reset_token_' . $request->username);
        if (!$cachedToken || $cachedToken !== $request->token) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired session. Please try again.'], 403);
        }

        // Update Password
        $user = User::where('username', $request->username)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Clear Token
        \Illuminate\Support\Facades\Cache::forget('reset_token_' . $request->username);

        return response()->json(['success' => true, 'message' => 'Password has been reset!']);
    }

    // --- LEGACY METHODS (Preserved but unused by new flow) ---
    public function sendResetLinkEmail(Request $request)
    {
        return $this->sendOtp($request->merge(['username' => User::where('email', $request->email)->value('username')]));
    }
}
