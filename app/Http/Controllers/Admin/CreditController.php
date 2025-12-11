<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    // 1. Show list of unpaid debts
    public function index()
    {
        // Get credits that are not fully paid, sorted by newest
        $credits = CustomerCredit::with('customer', 'sale')
                    ->where('is_paid', false)
                    ->latest()
                    ->get();

        return view('admin.credits.index', compact('credits'));
    }

    // 2. Process a repayment
    public function repay(Request $request, CustomerCredit $credit)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:1'
        ]);

        $amount = $request->payment_amount;

        // Check if payment exceeds balance
        if ($amount > $credit->remaining_balance) {
            return back()->withErrors(['payment_amount' => 'Payment cannot exceed remaining balance (₱' . number_format($credit->remaining_balance, 2) . ')']);
        }

        // Update the record
        $credit->amount_paid += $amount;
        $credit->remaining_balance -= $amount;

        // Check if fully paid
        if ($credit->remaining_balance <= 0) {
            $credit->is_paid = true;
            $credit->remaining_balance = 0;
        }

        $credit->save();

        return back()->with('success', 'Payment of ₱' . number_format($amount, 2) . ' recorded successfully!');
    }
}