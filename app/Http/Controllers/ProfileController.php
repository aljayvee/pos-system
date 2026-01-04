<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    protected $mpinService;

    public function __construct(\App\Services\Auth\MpinService $mpinService)
    {
        $this->mpinService = $mpinService;
    }

    // 1. Show Profile Page
    public function edit()
    {
        $user = Auth::user();
        $hasMpin = $this->mpinService->hasMpin($user);
        return view('admin.profile', compact('user', 'hasMpin'));
    }

    // 2. Update Profile
    public function update(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Profile Update Request', [
            'has_file' => $request->hasFile('photo'),
            'all' => $request->all(),
            'user_id' => Auth::id()
        ]);
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'current_password' => 'required|current_password', // Always required for security
            'password' => 'nullable|min:6|confirmed',
            'current_mpin' => 'nullable|required_with:mpin|digits_between:7,16', // Required if setting new MPIN
            'mpin' => 'nullable|digits_between:7,16|confirmed',
        ]);

        // Update basic info
        $user->name = $request->name;

        // Handle Photo Upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->profile_photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Store new photo
            $path = $request->file('photo')->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
        }

        // Update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Update MPIN if provided
        if ($request->filled('mpin')) {
            // Verify existing MPIN if one exists
            if ($this->mpinService->hasMpin($user)) {
                if (!$this->mpinService->verifyMpin($user, $request->current_mpin)) {
                    return back()->withErrors(['current_mpin' => 'The provided current MPIN is incorrect.']);
                }
            }

            $this->mpinService->setMpin($user, $request->mpin);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }
}