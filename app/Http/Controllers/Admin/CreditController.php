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

    // REPLACE the existing 'storePayment' method with this:
    
    public function storePayment(Request $request, $id) // Accept ID, not Model
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:255'
        ]);

        // Start ACID Transaction
        DB::beginTransaction();

        try {
            // 1. LOCK the record. 
            // This pauses any other process trying to touch this specific credit.
            $credit = CustomerCredit::where('id', $id)->lockForUpdate()->firstOrFail();

            // 2. Validate Fresh Data (Consistency)
            if ($credit->is_paid) {
                // Return error immediately if someone else just paid it
                DB::rollBack();
                return back()->with('error', 'This credit was already fully paid by another transaction just now.');
            }

            if ($request->amount > $credit->remaining_balance) {
                DB::rollBack();
                return back()->with('error', "Payment amount (₱{$request->amount}) cannot exceed current balance (₱{$credit->remaining_balance}).");
            }

            // 3. Process Payment (Atomicity)
            CreditPayment::create([
                'customer_credit_id' => $credit->id,
                'user_id' => Auth::id(),
                'amount' => $request->amount,
                'payment_date' => now(),
                'notes' => $request->notes
            ]);

            // 4. Update Balance
            // We use direct math here because we hold the lock.
            $credit->amount_paid += $request->amount;
            $credit->remaining_balance -= $request->amount;

            // Handle strict floating point comparisons
            if ($credit->remaining_balance <= 0.01) { 
                $credit->remaining_balance = 0;
                $credit->is_paid = true;
            }
            
            $credit->save();

            // 5. Log Activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Collected Payment',
                'description' => "Collected ₱{$request->amount} from {$credit->customer->name} for Transaction #{$credit->sale_id}"
            ]);

            DB::commit(); // Release Lock
            return back()->with('success', 'Payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing payment: ' . $e->getMessage());
        }
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