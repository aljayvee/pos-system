<!DOCTYPE html>
<html>
<head>
    <title>Receipt #{{ $sale->id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 300px; /* Standard thermal width */
            margin: 0 auto;
            padding: 10px;
            font-size: 12px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .border-bottom { border-bottom: 1px dashed #000; margin: 5px 0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th { text-align: left; border-bottom: 1px solid #000; }
        .footer { margin-top: 20px; font-size: 10px; }
        
        /* Hide buttons when printing */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    {{-- Fetch settings manually since not passed from controller --}}
    @php
        $storeName = \App\Models\Setting::where('key', 'store_name')->value('value') ?? 'Sari-Sari Store';
        $storeAddr = \App\Models\Setting::where('key', 'store_address')->value('value');
        $storeContact = \App\Models\Setting::where('key', 'store_contact')->value('value');
    @endphp

    <div class="text-center">
        <h3 style="margin: 0;">{{ $storeName }}</h3>
        @if($storeAddr)<p style="margin: 2px 0;">{{ $storeAddr }}</p>@endif
        @if($storeContact)<p style="margin: 2px 0;">{{ $storeContact }}</p>@endif
        <p>Official Receipt</p>
        <p>{{ $sale->created_at->format('M d, Y h:i A') }}</p>
    </div>

    <div class="border-bottom"></div>

    <div>
        <strong>Trans ID:</strong> #{{ $sale->id }}<br>
        <strong>Cashier:</strong> {{ $sale->user->name }}<br>
        <strong>Customer:</strong> {{ $sale->customer->name ?? 'Walk-in' }}
    </div>

    <div class="border-bottom"></div>

    <table class="table">
        <thead>
            <tr>
                <th>Qty</th>
                <th>Item</th>
                <th class="text-right">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $item)
            <tr>
                <td>{{ $item->quantity }}</td>
                <td>
                    {{ $item->product->name }} 
                    {{-- NEW: UNIT DISPLAY --}}
                    <small>({{ $item->product->unit }})</small>
                </td>
                <td class="text-right">{{ number_format($item->price * $item->quantity, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="border-bottom"></div>

    <div class="text-right">
        <p style="margin: 2px 0;">
            <strong>TOTAL: ₱{{ number_format($sale->total_amount, 2) }}</strong>
        </p>
        
        @if($sale->points_discount > 0)
            <p style="margin: 2px 0;" class="text-success">
                Points Discount: -₱{{ number_format($sale->points_discount, 2) }}
            </p>
        @endif

        @if($sale->payment_method == 'cash')
            <p style="margin: 2px 0;">Cash: ₱{{ number_format($sale->amount_paid, 2) }}</p>
            <p style="margin: 2px 0;">Change: ₱{{ number_format($sale->amount_paid - $sale->total_amount, 2) }}</p>
        @elseif($sale->payment_method == 'credit')
            <p style="margin: 2px 0;"><strong>PAID VIA CREDIT (UTANG)</strong></p>
        @else
            <p style="margin: 2px 0;">Paid via: {{ ucfirst($sale->payment_method) }}</p>
            <p style="margin: 2px 0;">Ref: {{ $sale->reference_number }}</p>
        @endif
    </div>

    <div class="footer text-center">
        <p>Thank you for buying!</p>
        <p>This serves as your official proof of payment.</p>
    </div>

    <div class="text-center no-print" style="margin-top: 20px;">
        <button onclick="window.print()">Print Again</button>
        <button onclick="window.close()">Close</button>
    </div>

</body>
</html>