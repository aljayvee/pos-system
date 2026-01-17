@php
    use Illuminate\Support\Facades\Crypt;
    use Illuminate\Contracts\Encryption\DecryptException;

    // 1. Fetch Store Details
    $store = $sale->user->store ?? \App\Models\Store::find(1);

    $storeName = $store->name ?? (\App\Models\Setting::where('key', 'store_name')->value('value') ?? 'Sari-Sari Store');
    $storeAddress = $store->address ?? (\App\Models\Setting::where('key', 'store_address')->value('value') ?? '');
    $storeContact = $store->contact_number ?? (\App\Models\Setting::where('key', 'store_contact')->value('value') ?? '');
    $storeOwner = $store->owner_name ?? '';

    $receiptFooter = \App\Models\Setting::where('key', 'receipt_footer')->value('value') ?? 'Thank you!';
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->id }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.2;
            margin: 0;
            padding: 10px;
            background: #fff;
            color: #000;
        }

        .receipt {
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .mb-1 {
            margin-bottom: 5px;
        }

        .border-bottom {
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table td {
            vertical-align: top;
            padding: 2px 0;
        }

        .qty {
            width: 15%;
        }

        .desc {
            width: 55%;
        }

        .price {
            width: 30%;
            text-align: right;
        }

        @media print {
            body {
                padding: 0;
                width: 100%;
            }

            .receipt {
                max-width: 100%;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="receipt">
        {{-- HEADER --}}
        <div class="text-center mb-1">
            <div class="fw-bold" style="font-size: 16px;">{{ $storeName }}</div>
            @if($storeOwner)
                <div style="font-size: 11px; text-transform: uppercase;">Prop. {{ $storeOwner }}</div>
            @endif
            <div>{{ $storeAddress }}</div>
            <div>{{ $storeContact }}</div>
        </div>

        {{-- TRANSACTION DETAILS --}}
        <div class="border-bottom" style="font-size: 11px;">
            <div>Date: {{ $sale->created_at->format('M d, Y h:i A') }}</div>
            <div>Ref #: {{ str_pad($sale->id, 8, '0', STR_PAD_LEFT) }}</div>
            <div>Cashier: {{ $sale->user->name }}</div>
            @if($sale->customer)
                <div>Cust: {{ $sale->customer->name }}</div>
            @endif
        </div>

        {{-- ITEMS --}}
        <table class="table mb-1">
            @foreach($sale->saleItems as $item)
                <tr>
                    <td class="qty">{{ $item->quantity }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="price">{{ number_format($item->quantity * $item->price, 2) }}</td>
                </tr>
            @endforeach
        </table>

        {{-- TOTALS --}}
        <div class="border-bottom mb-1">
            @if($sale->points_discount > 0)
                <div class="d-flex justify-content-between">
                    <span>Subtotal</span>
                    <span style="float:right">{{ number_format($sale->total_amount + $sale->points_discount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Discount</span>
                    <span style="float:right">-{{ number_format($sale->points_discount, 2) }}</span>
                </div>
            @endif

            <div class="fw-bold" style="font-size: 16px; margin-top: 5px;">
                <span>TOTAL</span>
                <span style="float: right;">₱{{ number_format($sale->total_amount, 2) }}</span>
            </div>
        </div>

        {{-- PAYMENT INFO --}}
        <div class="mb-1" style="font-size: 11px;">
            <div>Payment: {{ ucfirst($sale->payment_method) }}</div>
            @if($sale->payment_method == 'cash')
                <div>Cash: {{ number_format($sale->amount_paid, 2) }}</div>
                <div>Change: {{ number_format($sale->amount_paid - $sale->total_amount, 2) }}</div>
            @elseif($sale->payment_method == 'digital')
                <div>Ref #: {{ $sale->reference_number }}</div>
            @elseif($sale->payment_method == 'credit')
                <div>Bal Added: {{ number_format($sale->total_amount, 2) }}</div>
            @endif
        </div>

        {{-- FOOTER --}}
        <div class="text-center" style="margin-top: 15px;">
            <div class="fw-bold">{{ $receiptFooter }}</div>
            <div style="font-size: 10px; margin-top: 10px; font-weight: bold; text-transform: uppercase;">
                THIS IS NOT AN OFFICIAL RECEIPT
            </div>
        </div>

        <button onclick="window.print()" class="no-print"
            style="width: 100%; padding: 10px; margin-top: 15px; cursor: pointer; background: #000; color: white; border: none; font-weight: bold;">
            PRINT RECEIPT
        </button>
    </div>

</body>

</html>