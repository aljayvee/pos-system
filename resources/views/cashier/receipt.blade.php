<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->id }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0; padding: 20px; background: #f0f0f0; }
        .receipt { max-width: 300px; margin: 0 auto; background: #fff; padding: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .border-bottom { border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 5px; }
        .table { width: 100%; border-collapse: collapse; }
        .table td { vertical-align: top; }
        .qty { width: 30px; }
        .price { width: 60px; text-align: right; }
        @media print {
            body { background: none; padding: 0; }
            .receipt { box-shadow: none; width: 100%; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="receipt">
        {{-- STORE HEADER --}}
        <div class="text-center mb-2">
            <div class="fw-bold" style="font-size: 16px;">
                {{ \App\Models\Setting::where('key', 'store_name')->value('value') ?? 'Sari-Sari Store' }}
            </div>
            <div>{{ \App\Models\Setting::where('key', 'store_address')->value('value') ?? '' }}</div>
            <div>{{ \App\Models\Setting::where('key', 'store_contact')->value('value') ?? '' }}</div>
        </div>

        <div class="border-bottom">
            <div>Date: {{ $sale->created_at->format('M d, Y h:i A') }}</div>
            <div>Receipt #: {{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</div>
            <div>Cashier: {{ $sale->user->name }}</div>
            @if($sale->customer)
                <div>Customer: {{ $sale->customer->name }}</div>
            @endif
        </div>

        {{-- ITEMS --}}
        <table class="table mb-2">
            @foreach($sale->saleItems as $item)
            <tr>
                <td class="qty">{{ $item->quantity }}x</td>
                <td>
                    {{ $item->product->name }}
                    <div style="font-size: 10px; color: #555;">@ {{ number_format($item->price, 2) }}</div>
                </td>
                <td class="price">{{ number_format($item->quantity * $item->price, 2) }}</td>
            </tr>
            @endforeach
        </table>

        {{-- TOTALS --}}
        <div class="border-bottom mb-2">
            <div class="d-flex justify-content-between">
                <span>Subtotal</span>
                <span class="float-end">{{ number_format($sale->total_amount + ($sale->points_discount ?? 0), 2) }}</span>
            </div>
            
            @if($sale->points_discount > 0)
            <div class="d-flex justify-content-between">
                <span>Points Discount</span>
                <span class="float-end">-{{ number_format($sale->points_discount, 2) }}</span>
            </div>
            @endif

            <div class="fw-bold" style="font-size: 14px; margin-top: 5px;">
                <span>TOTAL</span>
                <span style="float: right;">₱{{ number_format($sale->total_amount, 2) }}</span>
            </div>
        </div>

        {{-- PAYMENT INFO --}}
        <div class="mb-2">
            <div>Payment: {{ ucfirst($sale->payment_method) }}</div>
            @if($sale->payment_method == 'cash')
                <div>Cash: {{ number_format($sale->amount_paid, 2) }}</div>
                <div>Change: {{ number_format($sale->amount_paid - $sale->total_amount, 2) }}</div>
            @elseif($sale->payment_method == 'digital')
                <div>Ref: {{ $sale->reference_number }}</div>
            @elseif($sale->payment_method == 'credit')
                <div style="font-style: italic;">Balance Added to Account</div>
            @endif
        </div>

        {{-- FOOTER --}}
        <div class="text-center" style="margin-top: 20px;">
            <div>{{ \App\Models\Setting::where('key', 'receipt_footer')->value('value') ?? 'Thank you!' }}</div>
            <div style="margin-top: 5px;">--- End of Transaction ---</div>
        </div>

        <button onclick="window.print()" class="no-print" style="width: 100%; padding: 10px; margin-top: 15px; cursor: pointer; background: #333; color: white; border: none;">Print Receipt</button>
    </div>

</body>
</html>