<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashRegisterAdjustment;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    public function index()
    {
        // Fetch only PENDING adjustments
        $adjustments = CashRegisterAdjustment::with(['user', 'session'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.approvals.index', compact('adjustments'));
    }
}
