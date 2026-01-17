<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockTransfer;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Store;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function index()
    {
        // History of transfers
        $transfers = StockTransfer::with(['product', 'fromStore', 'toStore', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.inventory.transfers.index', compact('transfers'));
    }

    public function create()
    {
        $products = Product::select('id', 'name', 'sku')->get();
        // For Source Store: If restricted, only their store. If Admin, all stores.
        $user = auth()->user();
        if ($user->role === 'admin') {
            $stores = Store::where('is_active', true)->get();
        } else {
            $stores = Store::where('id', $user->store_id)->get();
        }

        // Target Stores: All active stores
        $targetStores = Store::where('is_active', true)->get();

        return view('admin.inventory.transfers.create', compact('products', 'stores', 'targetStores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_store_id' => 'required|exists:stores,id',
            'to_store_id' => 'required|exists:stores,id|different:from_store_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        // Authorization Check
        $user = auth()->user();
        if ($user->role !== 'admin' && $user->store_id != $request->from_store_id) {
            return back()->with('error', 'Unauthorized: You can only transfer stock FROM your assigned store.');
        }

        DB::beginTransaction();
        try {
            // 1. Check Availability
            $sourceInventory = Inventory::where('store_id', $request->from_store_id)
                ->where('product_id', $request->product_id)
                ->first();

            if (!$sourceInventory || $sourceInventory->stock < $request->quantity) {
                return back()->with('error', 'Insufficient stock in source store.');
            }

            // 2. Decrement Source
            $sourceInventory->decrement('stock', $request->quantity);

            // 3. Increment Target (Create if missing)
            $targetInventory = Inventory::firstOrCreate(
                ['store_id' => $request->to_store_id, 'product_id' => $request->product_id],
                ['stock' => 0, 'reorder_point' => 10]
            );
            $targetInventory->increment('stock', $request->quantity);

            // 4. Log Transfer
            StockTransfer::create([
                'product_id' => $request->product_id,
                'from_store_id' => $request->from_store_id,
                'to_store_id' => $request->to_store_id,
                'quantity' => $request->quantity,
                'user_id' => auth()->id(),
                'notes' => $request->notes
            ]);

            // 5. Activity Log
            $product = Product::find($request->product_id);
            $fromStore = Store::find($request->from_store_id);
            $toStore = Store::find($request->to_store_id);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Stock Transfer',
                'description' => "Transferred {$request->quantity}x {$product->name} from {$fromStore->name} to {$toStore->name}."
            ]);

            DB::commit();

            return redirect()->route('transfers.index')->with('success', 'Stock transferred successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }
}
