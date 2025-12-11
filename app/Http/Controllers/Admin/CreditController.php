<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerCredit;
use App\Models\CreditPayment;
use App\Models\ActivityLog;
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

        // 2. Sort Logic
        if ($request->sort == 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->latest();
        }

        // 3. Filter by Overdue
        if ($request->filter == 'overdue') {
            $query->whereDate('due_date', '<', \Carbon\Carbon::now());
        }

        // 4. Get Total Receivables
        $totalReceivables = $query->sum('remaining_balance');

        $credits = $query->paginate(15)->withQueryString();

        return view('admin.credits.index', compact('credits', 'totalReceivables'));
    }

    // NEW: Process Debt Payment (Consolidated Method)
    public function storePayment(Request $request, CustomerCredit $credit)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:255'
        ]);

        if ($credit->is_paid) {
            return back()->with('error', 'This credit is already fully paid.');
        }

        if ($request->amount > $credit->remaining_balance) {
            return back()->with('error', 'Payment amount cannot exceed balance.');
        }

        DB::transaction(function () use ($request, $credit) {
            // 1. Record Payment Log
            CreditPayment::create([
                'customer_credit_id' => $credit->id,
                'user_id' => Auth::id(),
                'amount' => $request->amount,
                'payment_date' => now(),
                'notes' => $request->notes
            ]);

            // 2. Update Credit Record
            $credit->amount_paid += $request->amount;
            $credit->remaining_balance -= $request->amount;

            if ($credit->remaining_balance <= 0) {
                $credit->remaining_balance = 0;
                $credit->is_paid = true;
            }
            
            $credit->save();

            // 3. Log Activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Collected Payment',
                'description' => "Collected ₱{$request->amount} from {$credit->customer->name} for Transaction #{$credit->sale_id}"
            ]);
        });

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function history(CustomerCredit $credit)
    {
        $payments = CreditPayment::where('customer_credit_id', $credit->id)
                        ->with('user')
                        ->latest()
                        ->get();
                        
        return view('admin.credits.history', compact('credit', 'payments'));
    }

    public function paymentLogs()
    {
        $payments = CreditPayment::with(['credit.customer', 'user'])
                        ->latest('payment_date')
                        ->get();

        return view('admin.credits.logs', compact('payments'));
    }

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
            fputcsv($file, ['Credit ID', 'Customer Name', 'Date', 'Due Date', 'Original Amount', 'Paid', 'Balance']);

            foreach ($credits as $credit) {
                fputcsv($file, [
                    $credit->id,
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