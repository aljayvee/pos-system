<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashRegisterAdjustment;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    public function index()
    {
        // 1. Fetch PENDING adjustments (Action Items)
        $adjustments = CashRegisterAdjustment::with(['user', 'session'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Fetch Session History (Logs) - Latest 50
        $logs = \App\Models\CashRegisterSession::with(['user'])
            ->where('status', 'closed')
            ->orderBy('closed_at', 'desc')
            ->limit(50)
            ->get();

        return view('admin.approvals.index', compact('adjustments', 'logs'));
    }
}
