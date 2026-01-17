<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RoleChangeRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\ActivityLog;

class ApprovalController extends Controller
{
    // 1. Manager: Send Approval Request
    public function sendRequest(Request $request)
    {
        $request->validate([
            'approver_id' => 'required|exists:users,id',
            'target_user_id' => 'required|exists:users,id',
            'new_role' => 'required|in:admin,cashier,manager,supervisor,stock_clerk,auditor'
        ]);

        $approver = User::find($request->approver_id);
        if ($approver->role !== 'admin' || !$approver->is_active) {
            return response()->json(['success' => false, 'message' => 'Invalid approver.']);
        }

        $approvalRequest = RoleChangeRequest::create([
            'requester_id' => auth()->id(),
            'approver_id' => $approver->id,
            'target_user_id' => $request->target_user_id,
            'new_role' => $request->new_role,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(10)
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Request Approval',
            'description' => "Requested approval from {$approver->name} to promote user #{$request->target_user_id} to {$request->new_role}."
        ]);

        // Broadcast Event
        \App\Events\ApprovalRequestCreated::dispatch($approvalRequest);

        return response()->json(['success' => true, 'request_id' => $approvalRequest->id]);
    }

    // 2. Manager: Poll Status (Waiting Room)
    public function checkStatus($id)
    {
        $approvalRequest = RoleChangeRequest::find($id);

        if (!$approvalRequest) {
            // Use case: Request was rejected and the associated user was deleted (cascade delete),
            // so the request itself is gone. We treat this as "rejected" to stop the polling.
            return response()->json(['status' => 'rejected']);
        }

        if ($approvalRequest->status === 'approved') {
            // Generate Token for Manager to submit
            $payload = [
                'approver_id' => $approvalRequest->approver_id,
                'requester_id' => $approvalRequest->requester_id,
                'timestamp' => now()->timestamp, // fresh timestamp
                'nonce' => \Illuminate\Support\Str::random(8)
            ];
            $token = base64_encode(json_encode($payload)) . '.' . hash_hmac('sha256', json_encode($payload), env('APP_KEY'));

            return response()->json(['status' => 'approved', 'token' => $token]);
        }

        if ($approvalRequest->status === 'rejected') {
            return response()->json(['status' => 'rejected']);
        }

        if ($approvalRequest->expires_at < now()) {
            return response()->json(['status' => 'expired']);
        }

        return response()->json(['status' => 'pending']);
    }

    // 3. Admin: Get Pending Requests (Notification Polling)
    public function getPending()
    {
        // Only Admins
        if (auth()->user()->role !== 'admin')
            return response()->json([]);

        $pending = RoleChangeRequest::with(['requester', 'targetUser'])
            ->where('approver_id', auth()->id())
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->get();

        return response()->json(['requests' => $pending]);
    }

    // 4. Admin: Approve/Reject
    public function decideRequest(Request $request, $id)
    {
        $approvalRequest = RoleChangeRequest::findOrFail($id);

        // Validation
        if ($approvalRequest->approver_id !== auth()->id())
            abort(403);
        if ($approvalRequest->expires_at < now())
            return response()->json(['success' => false, 'message' => 'Request expired.']);

        $decision = $request->input('decision'); // 'approve' or 'reject'

        if ($decision === 'approve') {
            // Verify Password
            if (!Hash::check($request->password, auth()->user()->password)) {
                return response()->json(['success' => false, 'message' => 'Invalid password.']);
            }

            $approvalRequest->update(['status' => 'approved']);

            // Activate User if this was a creation request
            $targetUser = $approvalRequest->targetUser;
            $logAction = 'Approve Role Change';
            $logDesc = "Approved role change for request #{$id}.";

            if (!$targetUser->is_active) {
                $targetUser->update(['is_active' => true]);
                $logAction = 'Approve User Creation';
                $logDesc = "Approved creation of new user {$targetUser->name} ({$targetUser->email}).";
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $logAction,
                'description' => $logDesc
            ]);
        } else {
            $approvalRequest->update(['status' => 'rejected']);

            // Cleanup: If the user was inactive (newly created) and rejected, delete them.
            $targetUser = $approvalRequest->targetUser;
            if (!$targetUser->is_active) {
                $targetUser->delete();
                $logDesc = "Rejected creation of new user {$targetUser->name} ({$targetUser->email}) and deleted account.";
            } else {
                $logDesc = "Rejected role change for request #{$id}.";
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Rejected Request',
                'description' => $logDesc
            ]);
        }

        return response()->json(['success' => true]);
    }
}
