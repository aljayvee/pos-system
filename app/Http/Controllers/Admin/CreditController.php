<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerCredit;
use App\Models\CreditPayment; // Import this
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CreditController extends Controller
{
    public function index()
    {
        $credits = CustomerCredit::with('customer', 'sale')
                    ->where('is_paid', false)
                    ->latest()
                    ->get();

        return view('admin.credits.index', compact('credits'));
    }

    // Updated Repay Function
    public function repay(Request $request, CustomerCredit $credit)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:1'
        ]);

        $amount = $request->payment_amount;

        if ($amount > $credit->remaining_balance) {
            return back()->withErrors(['payment_amount' => 'Payment cannot exceed balance.']);
        }

        DB::transaction(function () use ($credit, $amount, $request) {
            // 1. Update Credit Record
            $credit->amount_paid += $amount;
            $credit->remaining_balance -= $amount;

            if ($credit->remaining_balance <= 0) {
                $credit->is_paid = true;
                $credit->remaining_balance = 0;
            }
            $credit->save();

            // 2. Log the Payment History (NEW)
            CreditPayment::create([
                'customer_credit_id' => $credit->credit_id,
                'amount' => $amount,
                'payment_date' => now(),
                'user_id' => Auth::id(),
                'notes' => 'Partial Payment'
            ]);
        });

        return back()->with('success', 'Payment recorded successfully!');
    }

    // NEW: Show Payment History
    public function history(CustomerCredit $credit)
    {
        $payments = CreditPayment::where('customer_credit_id', $credit->credit_id)
                        ->with('user')
                        ->latest()
                        ->get();
                        
        return view('admin.credits.history', compact('credit', 'payments'));
    }

    // NEW: Global Payment Logs (Sidebar Feature)
    public function paymentLogs()
    {
        // Fetch all payments with related Credit/Customer info
        $payments = \App\Models\CreditPayment::with(['credit.customer', 'user'])
                        ->latest('payment_date')
                        ->get();

        return view('admin.credits.logs', compact('payments'));
    }
}