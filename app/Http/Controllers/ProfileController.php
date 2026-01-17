<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ProfileController extends Controller
{
    protected $mpinService;
    protected $otpService;
    protected $notifier;

    public function __construct(
        \App\Services\Auth\MpinService $mpinService,
        \App\Services\Otp\OtpServiceInterface $otpService,
        \App\Services\Notification\OtpNotificationService $notifier
    ) {
        $this->mpinService = $mpinService;
        $this->otpService = $otpService;
        $this->notifier = $notifier;
    }

    // 1. Show Profile Page
    public function edit()
    {
        $user = Auth::user();
        $hasMpin = $this->mpinService->hasMpin($user);
        return view('admin.profile', compact('user', 'hasMpin'));
    }

    // 2. Update Basic Info (Name, Birthdate, Gender)
    public function updateInfo(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . Auth::id(),
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|string|in:Male,Female,Other',
        ]);

        $user = Auth::user();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->birthdate = $request->birthdate;
        $user->gender = $request->gender;
        $user->save();

        return back()->with('success', 'Profile details updated successfully.');
    }

    // 3. Update Photo
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::user();

        if ($user->profile_photo_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo_path);
        }

        $path = $request->file('photo')->store('profile-photos', 'public');
        $user->profile_photo_path = $path;
        $user->save();

        return back()->with('success', 'Profile photo updated.');
    }

    // LEGACY: Update Security (Password & MPIN) - Kept for fallback/legacy calls
    public function updateSecurity(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'nullable|min:6|confirmed',
            'current_mpin' => 'nullable|required_with:mpin|digits_between:7,16',
            'mpin' => 'nullable|digits_between:7,16|confirmed',
        ]);

        // Update Password
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Update MPIN
        if ($request->filled('mpin')) {
            if ($this->mpinService->hasMpin($user)) {
                if (!$this->mpinService->verifyMpin($user, $request->current_mpin)) {
                    return back()->withErrors(['current_mpin' => 'The provided current MPIN is incorrect.']);
                }
            }
            $this->mpinService->setMpin($user, $request->mpin);
        }

        $user->save();

        return back()->with('success', 'Security settings updated successfully.');
    }

    // ==========================================
    // OTP METHODS
    // ==========================================

    // 3. Initiate Email Verification
    public function initiateEmailVerification(Request $request)
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['success' => false, 'message' => 'Email is already verified.']);
        }

        $code = $this->otpService->generate($user->email, 'email_verification');

        // Send OTP
        try {
            $this->notifier->sendViaEmail($user, $code, 'Verify Your Email');
            return response()->json(['success' => true, 'message' => 'Verification code sent to your email.']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Email Verification OTP Send Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send verification email. Please try again.']);
        }
    }

    // 4. Check Email Verification
    public function checkEmailVerification(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);
        $user = Auth::user();

        if ($this->otpService->validate($user->email, $request->code, 'email_verification')) {
            $user->markEmailAsVerified();
            return response()->json(['success' => true, 'message' => 'Email verified successfully!']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid or expired verification code.']);
    }

    // ==========================================
    // SECURE EMAIL CHANGE FLOW
    // ==========================================

    // Step 1: Verify Password & Send OTP to Current Email
    public function initiateEmailChange(Request $request)
    {
        $request->validate(['password' => 'required|current_password']);

        $user = Auth::user();

        // Generate OTP for current email to prove ownership access
        $code = $this->otpService->generate($user->email, 'email_change_current');

        try {
            $this->notifier->sendViaEmail($user, $code, 'Security Verification: Email Change Request');
            return response()->json(['success' => true, 'message' => 'Password verified. OTP sent to your current email.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send OTP.']);
        }
    }

    // Step 2: Verify Current OTP
    public function verifyCurrentEmailOtp(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);
        $user = Auth::user();

        if ($this->otpService->validate($user->email, $request->otp, 'email_change_current')) {
            // Mark step 1 as complete only in session/cache if needed, 
            // but for now we trust the client to move to step 3 immediately 
            // (real security would require server-side state tracking here)
            session(['email_change_verified_current' => true]);
            return response()->json(['success' => true, 'message' => 'Current email verified. Please enter new email.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid OTP.']);
    }

    // Step 3: Request OTP for New Email
    public function requestNewEmailOtp(Request $request)
    {
        if (!session('email_change_verified_current')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized sequence.']);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'new_email' => 'required|email|unique:users,email'
        ]);

        if ($validator->fails()) {
            if ($validator->errors()->has('new_email')) {
                $errors = $validator->errors()->get('new_email');
                foreach ($errors as $error) {
                    if (str_contains(strtolower($error), 'taken') || str_contains(strtolower($error), 'exists')) {
                        return response()->json(['success' => false, 'message' => 'Email Already Taken']);
                    }
                }
                return response()->json(['success' => false, 'message' => $errors[0]]);
            }
            return response()->json(['success' => false, 'message' => 'Invalid email address.']);
        }

        $newEmail = $request->new_email;
        $code = $this->otpService->generate($newEmail, 'email_change_new'); // Key by new email!

        // Store temp email in session for final step
        session(['temp_new_email' => $newEmail]);

        try {
            // Hack for Notifier Service if it requires User model:
            $tempUser = clone Auth::user();
            $tempUser->email = $newEmail;
            $this->notifier->sendViaEmail($tempUser, $code, 'Verify Your New Email Address');

            return response()->json(['success' => true, 'message' => 'OTP sent to ' . $newEmail]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send OTP to new email.']);
        }
    }

    // Step 4: Confirm New Email & Update
    public function confirmNewEmail(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);

        if (!session('email_change_verified_current') || !session('temp_new_email')) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please start over.']);
        }

        $newEmail = session('temp_new_email');

        if ($this->otpService->validate($newEmail, $request->otp, 'email_change_new')) {
            $user = Auth::user();
            $oldEmail = $user->email;

            // UPDATE EMAIL
            $user->email = $newEmail;
            $user->email_verified_at = now(); // Mark new email as verified since we just did it
            $user->save();

            // Cleanup
            session()->forget(['email_change_verified_current', 'temp_new_email']);

            // Optional: Notify OLD email
            try {
                $oldUserStub = clone $user;
                $oldUserStub->email = $oldEmail;
                $this->notifier->sendViaEmail($oldUserStub, 'Your account email was just changed to ' . $newEmail, 'Security Alert: Email Changed');
            } catch (\Exception $e) {
                // Non-blocking
            }

            return response()->json(['success' => true, 'message' => 'Email updated successfully!']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid OTP.']);
    }

    // ==========================================
    // SECURE PASSWORD CHANGE (OTP-Based)
    // ==========================================

    public function requestPasswordOtp()
    {
        $user = Auth::user();
        $code = $this->otpService->generate($user->email, 'password_change');

        try {
            $this->notifier->sendViaEmail($user, $code, 'Verification Code for Password Change');
            return response()->json(['success' => true, 'message' => 'OTP sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send OTP.']);
        }
    }

    public function verifyPasswordOtp(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);
        $user = Auth::user();

        if ($this->otpService->validate($user->email, $request->otp, 'password_change')) {
            // Set session flag to allow update
            session(['password_change_verified' => true]);
            return response()->json(['success' => true, 'message' => 'OTP Verified. Please set your new password.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.']);
    }

    public function updatePasswordViaOtp(Request $request)
    {
        if (!session('password_change_verified')) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please verify OTP again.']);
        }

        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        // Clear session
        session()->forget('password_change_verified');

        // Notify User
        try {
            // Get location/device info
            $ip = $request->ip();
            $ua = $request->userAgent();
            $details = "IP: {$ip}\nDevice: {$ua}";

            Mail::raw("Your password was successfully changed.\n\nDetails:\n{$details}\n\nIf this was not you, please contact support immediately.", function ($message) use ($user) {
                $message->to($user->email)->subject('Security Alert: Password Changed');
            });
        } catch (\Exception $e) {
            // Non-blocking
        }

        return response()->json(['success' => true, 'message' => 'Password changed successfully.']);
    }


    // ==========================================
    // SECURE MPIN CHANGE (OTP-Based)
    // ==========================================

    public function requestMpinOtp()
    {
        $user = Auth::user();
        $code = $this->otpService->generate($user->email, 'mpin_change');

        try {
            $this->notifier->sendViaEmail($user, $code, 'Verification Code for MPIN Change');
            return response()->json(['success' => true, 'message' => 'OTP sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send OTP.']);
        }
    }

    public function verifyMpinOtp(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);
        $user = Auth::user();

        if ($this->otpService->validate($user->email, $request->otp, 'mpin_change')) {
            session(['mpin_change_verified' => true]);
            return response()->json(['success' => true, 'message' => 'OTP Verified. Please set your new MPIN.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.']);
    }

    public function updateMpinViaOtp(Request $request)
    {
        if (!session('mpin_change_verified')) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please verify OTP again.']);
        }

        $request->validate([
            'mpin' => 'required|digits_between:7,16|confirmed',
        ]);

        $user = Auth::user();
        $this->mpinService->setMpin($user, $request->mpin);
        // Clear session
        session()->forget('mpin_change_verified');

        // Notify User
        try {
            Mail::raw("Your MPIN was successfully changed.\n\nIf this was not you, please contact support immediately.", function ($message) use ($user) {
                $message->to($user->email)->subject('Security Alert: MPIN Changed');
            });
        } catch (\Exception $e) {
            // Non-blocking
        }

        return response()->json(['success' => true, 'message' => 'MPIN changed successfully.']);
    }
}