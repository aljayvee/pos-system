<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $storeId = $this->getActiveStoreId();
        
        // Show users belonging to this Store OR users with no store (if any)
        $users = \App\Models\User::where('store_id', $storeId)->get();
        
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {

        // ... validation ...
        $data = $request->all();
        $data['password'] = bcrypt($data['password']);
        $data['store_id'] = $this->getActiveStoreId(); // <--- Force assign to current branch

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed', // expects password_confirmation field
            'role' => 'required|in:admin,cashier'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true
        ]);

        \App\Models\User::create($data);
        return back()->with('success', 'User created for this branch.');
    }

    // --- NEW METHODS ---

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // LOG ACTION (Log before delete so we get the name)
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Deleted User',
            'description' => "Permanently deleted user: {$user->name} ({$user->email})"
        ]);

        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }
    
    // Optional: Toggle Active Status
    public function toggleStatus(User $user)
    {

        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User has been $status.");
    }

    // ... inside UserController ...

    // NEW: Update User (Name, Email, Role, Password)
    public function update(Request $request, User $user)
    {

        // 1. Get Current Admin's Branch
        $currentStoreId = $this->getActiveStoreId();

        // 2. Strict Check: Prevent editing users from other branches
        // (Unless you are in Main Store #1, which might act as Super Admin)
        if ($user->store_id != $currentStoreId && $currentStoreId != 1) {
            abort(403, 'Unauthorized. You can only manage users in your own branch.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,cashier',
            'password' => 'nullable|min:6' // Password is optional here
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        // Only hash and update password if a new one is provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // SECURITY CHECK
        if ($user->store_id != $this->getActiveStoreId()) {
            abort(403, 'Unauthorized action. You cannot edit users from another branch.');
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }
}