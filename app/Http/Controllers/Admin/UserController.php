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
        
        // FIX: Use paginate() instead of get() for consistency
        $users = \App\Models\User::where('store_id', $storeId)
                    ->latest()
                    ->paginate(10);
        
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed', 
            'role' => 'required|in:admin,cashier,manager,supervisor,stock_clerk,auditor'
        ]);

        // FIX: Create User ONCE. Removed the duplicate User::create calls.
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'store_id' => $this->getActiveStoreId(), // Force assign branch
            'is_active' => true
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        // Get permissions for the user's current role to show "Default" status
        $rawPerms = \Illuminate\Support\Facades\Config::get('role_permission.' . $user->role, []);
        $rolePermissions = [];
        foreach ($rawPerms as $perm) {
            $rolePermissions[] = ($perm instanceof \BackedEnum) ? $perm->value : $perm;
        }

        return view('admin.users.edit', compact('user', 'rolePermissions'));
    }

    public function update(Request $request, User $user)
    {
        // Strict Branch Check
        $currentStoreId = $this->getActiveStoreId();
        if ($user->store_id != $currentStoreId && $currentStoreId != 1) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,cashier,manager,supervisor,stock_clerk,auditor', // Updated roles
            'password' => 'nullable|min:6',
            'permissions' => 'nullable|array'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // SECURITY: Prevent non-admins from changing their own Role or Permissions
        $isSelf = auth()->id() === $user->id;
        $isAdmin = auth()->user()->role === 'admin';

        if (!$isSelf || $isAdmin) {
            // Allowed to update Role
            $data['role'] = $request->role;
            
            // Allowed to update Permissions
            if ($request->has('permissions')) {
                $perms = collect($request->permissions)
                    ->filter(fn($val) => !is_null($val))
                    ->map(fn($val) => (bool) $val)
                    ->toArray();
                
                $data['permissions'] = !empty($perms) ? $perms : null;
            }
        } elseif ($request->role !== $user->role) {
             // If self (non-admin) tries to change role, fail or ignore? 
             // Form validation passed, but logic denies.
             return back()->with('error', 'Security Alert: You cannot change your own role.');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Deleted User',
            'description' => "Permanently deleted user: {$user->name} ({$user->email})"
        ]);

        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }
    
    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User has been $status.");
    }
}