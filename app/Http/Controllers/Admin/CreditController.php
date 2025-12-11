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
    public function index(Request $request)
    {
        $query = CustomerCredit::with('customer', 'sale')
                    ->where('is_paid', false);

        // 1. Search by Customer Name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }

        // 2. Sort Logic (Default: Oldest due first, or Newest created)
        if ($request->sort == 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->latest(); // Default: Newest first
        }

        // 3. NEW: Filter by Overdue
        if ($request->filter == 'overdue') {
            $query->whereDate('due_date', '<', \Carbon\Carbon::now());
        }

        // 4. Get Total Receivables (Sum of remaining balances in this filtered view)
        $totalReceivables = $query->sum('remaining_balance');

        $credits = $query->paginate(15)->withQueryString();

        return view('admin.credits.index', compact('credits', 'totalReceivables'));
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

    // NEW: Export Credits to CSV
    public function export()
    {
        $credits = CustomerCredit::with('customer')->where('is_paid', false)->get();

        $filename = "outstanding_credits_" . date('Y-m-d') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($credits) {
            $file = fopen('php://output', 'w');
            // Header Row
            fputcsv($file, ['Credit ID', 'Customer Name', 'Date Incurred', 'Due Date', 'Original Amount', 'Amount Paid', 'Remaining Balance']);

            foreach ($credits as $credit) {
                fputcsv($file, [
                    $credit->credit_id, // Ensure this matches your primary key name
                    $credit->customer->name ?? 'Unknown',
                    $credit->created_at->format('Y-m-d'),
                    $credit->due_date ?? 'N/A',
                    $credit->total_amount,
                    $credit->amount_paid,
                    $credit->remaining_balance
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}