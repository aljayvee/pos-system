<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Models\Store;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $storeId = $this->getActiveStoreId();
        $query = \App\Models\User::where('store_id', $storeId);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $admins = User::where('role', 'admin')->where('is_active', true)->get(['id', 'name', 'email']);
        $stores = Store::all(); // Pass stores
        return view('admin.users.create', compact('admins', 'stores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'nullable|string|email|max:255|unique:users',
            'role' => 'required|string',
            'assigned_branch' => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'approver_id' => 'nullable|exists:users,id'
        ]);

        // STRICT RULE: Manager cannot create Admin (Backend Check)
        if (auth()->user()->role === 'manager' && $request->role === 'admin') {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Managers cannot create Admin accounts.']);
            }
            return back()->with('error', 'Security Alert: Managers cannot create Admin accounts.');
        }

        $isActive = true;
        $isPendingApproval = false;

        // Manager Creating User -> Require Approval
        if (auth()->user()->role === 'manager') {
            $isActive = false;
            $isPendingApproval = true;

            if (!$request->approver_id) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Please select an administrator to approve this request.']);
                }
                return back()->with('error', 'Administrator selection is required.');
            }
        }

        $fullName = $request->first_name . ' ' . $request->last_name;

        $user = User::create([
            'name' => $request->first_name . ' ' . $request->last_name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'assigned_branch' => $request->assigned_branch,
            'password' => Hash::make($request->password),
            'birthdate' => $request->birthdate,
            // 'age' => $request->age, // Removed
            'gender' => $request->gender,
            'permissions' => [],
            // Allow Admin to set store, else default to current context
            'store_id' => auth()->user()->role === 'admin' ? ($request->store_id ?? $this->getActiveStoreId()) : $this->getActiveStoreId(),
            'is_active' => $isActive
        ]);

        if ($isPendingApproval) {
            // Create Approval Request
            $approvalRequest = \App\Models\RoleChangeRequest::create([
                'requester_id' => auth()->id(),
                'approver_id' => $request->approver_id,
                'target_user_id' => $user->id,
                'new_role' => $request->role, // Store intended role
                'status' => 'pending',
                'expires_at' => now()->addMinutes(10)
            ]);

            // Broadcast Event
            \App\Events\ApprovalRequestCreated::dispatch($approvalRequest);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'pending_approval' => true,
                    'request_id' => $approvalRequest->id
                ]);
            }
            // Fallback for non-JS (should not happen with new UI)
            return redirect()->route('users.index')->with('warning', 'User created but requires Admin approval to activate.');
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        // STRICT RULE: Manager cannot edit Admin
        if (auth()->user()->role === 'manager' && $user->role === 'admin') {
            return redirect()->route('users.index')->with('error', 'Security Alert: Managers cannot edit Admin accounts.');
        }

        // Get permissions for the user's current role to show "Default" status
        $rawPerms = \Illuminate\Support\Facades\Config::get('role_permission.' . $user->role, []);
        $rolePermissions = [];
        foreach ($rawPerms as $perm) {
            $rolePermissions[] = ($perm instanceof \BackedEnum) ? $perm->value : $perm;
        }

        // Fetch list of Admins for Approval Modal
        $admins = User::where('role', 'admin')->where('is_active', true)->get(['id', 'name', 'email']);
        $stores = Store::all();

        return view('admin.users.edit', compact('user', 'rolePermissions', 'admins', 'stores'));
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
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,cashier,manager,supervisor,stock_clerk,auditor', // Updated roles
            'password' => 'nullable|min:6',
            'permissions' => 'nullable|array',
            'store_id' => 'nullable|exists:stores,id'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // SECURITY: Prevent non-admins from changing their own Role or Permissions
        $isSelf = auth()->id() === $user->id;
        $isAdmin = auth()->user()->role === 'admin';
        $isManager = auth()->user()->role === 'manager';

        // STRICT RULE: Manager cannot edit Admin
        if ($isManager && $user->role === 'admin') {
            return redirect()->route('users.index')->with('error', 'Security Alert: Managers cannot edit Admin accounts.');
        }

        // STRICT RULE: Manager cannot promote/demote ANY role without approval
        if ($isManager && $request->role !== $user->role) {
            // Check for Approval Token
            if ($request->filled('admin_approval_token')) {
                $valid = $this->verifyApprovalToken($request->admin_approval_token);
                if (!$valid) {
                    return back()->with('error', 'Security Alert: Invalid or expired admin approval.');
                }
                // Allowed!
            } else {
                return back()->with('error', 'Security Alert: Managers require Admin approval to change user roles.');
            }
        }

        if (!$isSelf || $isAdmin) {
            // STRICT RULE: Manager cannot promote to Admin
            if (auth()->user()->role === 'manager' && $request->role === 'admin') {
                return back()->with('error', 'Security Alert: Managers cannot promote users to Admin.');
            }

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

        // Allow Admin to update store_id
        if ($isAdmin && $request->has('store_id')) {
            $data['store_id'] = $request->store_id;
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // STRICT RULE: Manager cannot delete Admin
        if (auth()->user()->role === 'manager' && $user->role === 'admin') {
            return back()->with('error', 'Security Alert: Managers cannot delete Admin accounts.');
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

        // STRICT RULE: Manager cannot toggle Admin
        if (auth()->user()->role === 'manager' && $user->role === 'admin') {
            return back()->with('error', 'Security Alert: Managers cannot deactivate Admin accounts.');
        }

        // ADVANCED PERMISSION CHECK: user.unlock
        // Only enforce if we are reactivating (unlocking) the user
        // Deactivating (locking) might still be generic 'user.manage'
        if (!$user->is_active) {
            if (!auth()->user()->hasPermission(\App\Enums\Permission::USER_UNLOCK)) {
                return back()->with('error', 'Security Alert: You do not have permission to unlock users.');
            }
        }

        // OPTIONAL: Also restrict locking? 
        // Usually "Unlock" is the sensitive action. "Locking" might be needed for urgent security by any manager.
        // Adhering to strict interpretation of 'user.unlock' -> specifically for UNLOCKING.

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User has been $status.");
    }

    // --- ADMIN APPROVAL HELPERS ---

    public function verifyOverride(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|exists:users,id',
            'password' => 'required'
        ]);

        $admin = User::find($request->admin_id);

        if ($admin->role !== 'admin' || !$admin->is_active) {
            return response()->json(['success' => false, 'message' => 'Selected user is not an active Admin.']);
        }

        if (!Hash::check($request->password, $admin->password)) {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Security Alert',
                'description' => 'Failed Admin Override attempt using account: ' . $admin->name
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid password.']);
        }

        // Generate Signed Token
        $payload = [
            'approver_id' => $admin->id,
            'requester_id' => auth()->id(),
            'timestamp' => now()->timestamp,
            'nonce' => \Illuminate\Support\Str::random(8)
        ];

        $token = base64_encode(json_encode($payload)) . '.' . hash_hmac('sha256', json_encode($payload), env('APP_KEY'));

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Admin Override',
            'description' => 'Authorized by Admin: ' . $admin->name
        ]);

        return response()->json(['success' => true, 'token' => $token]);
    }

    // --- ARCHIVED USERS ---

    public function archived()
    {
        $storeId = $this->getActiveStoreId();
        // Get only trashed users
        $users = \App\Models\User::onlyTrashed()
            ->where('store_id', $storeId)
            ->latest('deleted_at')
            ->paginate(10);

        return view('admin.users.archived', compact('users'));
    }

    public function restore($id)
    {
        $user = \App\Models\User::withTrashed()->findOrFail($id);
        $user->restore();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Restored User',
            'description' => "Restored user: {$user->name} ({$user->email})"
        ]);

        return redirect()->route('users.archived')->with('success', 'User restored successfully.');
    }

    public function forceDelete($id)
    {
        $user = \App\Models\User::withTrashed()->findOrFail($id);

        // Prevent self-delete or super admin delete if needed
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete yourself.');
        }

        $user->forceDelete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Force Deleted User',
            'description' => "Permanently deleted user: {$user->name} ({$user->email})"
        ]);

        return redirect()->route('users.archived')->with('success', 'User permanently deleted.');
    }

    protected function verifyApprovalToken($token)
    {
        try {
            [$payloadJson, $hash] = explode('.', $token);
            $payload = json_decode(base64_decode($payloadJson), true);

            // Verify HMAC
            $expectedHash = hash_hmac('sha256', base64_decode($payloadJson), env('APP_KEY'));
            if (!hash_equals($expectedHash, $hash))
                return false;

            // Verify Timestamp (valid for 5 mins)
            if (now()->timestamp - $payload['timestamp'] > 300)
                return false;

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}