<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Services\CashRegisterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashRegisterController extends Controller
{
    protected $service;

    public function __construct(CashRegisterService $service)
    {
        $this->service = $service;
    }

    // 1. GET STATUS (Is Register Open?)
    public function status()
    {
        $storeId = session('active_store_id', auth()->user()->store_id ?? 1);
        $session = $this->service->getCurrentSession($storeId);

        if ($session) {
            return response()->json(['status' => 'open', 'session' => $session]);
        }
        return response()->json(['status' => 'closed']);
    }

    // 2. OPEN REGISTER
    public function open(Request $request)
    {
        // [SECURITY] Only Admins can open the register
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only Admins can open the register.'], 403);
        }

        $request->validate(['opening_amount' => 'required|numeric|min:0']);

        $storeId = session('active_store_id', auth()->user()->store_id ?? 1);
        
        try {
            $session = $this->service->openSession($storeId, Auth::id(), $request->opening_amount);
            return response()->json(['success' => true, 'session' => $session]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // 3. CLOSE REGISTER
    public function close(Request $request)
    {
        // [SECURITY] Anyone with a valid session can close it (Cashier/Admin)
        // Checks are handled by service ensuring the session belongs to user (or user is admin)

        $request->validate([
            'closing_amount' => 'required|numeric|min:0',
            'session_id' => 'required|exists:cash_register_sessions,id'
        ]);

        try {
            // Optional: Check if closing user matches opening user? Or allow anyone to close?
            // "Shared Session" = Anyone can close.
            
            $session = $this->service->closeSession($request->session_id, $request->closing_amount, $request->notes);
            return response()->json(['success' => true, 'session' => $session]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // 4. MANAGER REQUEST ADJUSTMENT
    public function requestAdjustment(Request $request)
    {
        // Permission Check
        if (!in_array(Auth::user()->role, ['manager', 'admin'])) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'session_id' => 'required|exists:cash_register_sessions,id',
            'new_amount' => 'required|numeric|min:0',
            'reason' => 'required|string|min:5'
        ]);

        try {
            $this->service->requestAdjustment(
                $request->session_id, 
                Auth::id(), 
                $request->new_amount, 
                $request->reason
            );
            return response()->json(['success' => true, 'message' => 'Adjustment requested successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // 5. ADMIN APPROVE/REJECT ADJUSTMENT
    public function processAdjustment(Request $request, $id)
    {
        // Permission Check (Strict Admin)
        if (Auth::user()->role !== 'admin') {
             return response()->json(['message' => 'Unauthorized. Only Admins can approve.'], 403);
        }

        $request->validate(['action' => 'required|in:approve,reject']);

        try {
            $this->service->processAdjustment($id, Auth::id(), $request->action);
            return response()->json(['success' => true, 'message' => 'Request processed successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
