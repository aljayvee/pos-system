@php
    use Illuminate\Support\Facades\Crypt;
    use Illuminate\Contracts\Encryption\DecryptException;

    // 1. Fetch Store Details
    $store = $sale->user->store ?? \App\Models\Store::find(1);

    // Settings (with Fallbacks)
    $storeName = $store->name ?? (\App\Models\Setting::where('key', 'store_name')->value('value') ?? 'Sari-Sari Store');
    $storeAddress = $store->address ?? (\App\Models\Setting::where('key', 'store_address')->value('value') ?? '');
    $storeContact = $store->contact_number ?? (\App\Models\Setting::where('key', 'store_contact')->value('value') ?? '');
    $storeOwner = $store->owner_name ?? '';

    // BIR Fields from Store Table (Added in Phase 1)
    $serialNumber = $store->serial_number ?? '';
    $minNumber = $store->min_number ?? '';
    $ptuNumber = $store->ptu_number ?? '';

    // Settings (Tax)
    $taxType = \App\Models\Setting::where('key', 'tax_type')->value('value') ?? 'inclusive';
    $taxRate = \App\Models\Setting::where('key', 'tax_rate')->value('value') ?? 0;

    // Receipt Footer
    $receiptFooter = \App\Models\Setting::where('key', 'receipt_footer')->value('value') ?? 'Thank you! Please come again.';
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.1;
            margin: 0;
            padding: 5px;
            background: #fff;
            color: #000;
        }

        .receipt {
            width: 100%;
            max-width: 350px;
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
            margin-bottom: 4px;
        }

        .border-bottom {
            border-bottom: 1px dashed #000;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table td {
            vertical-align: top;
            padding: 1px 0;
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

        .tax-breakdown {
            font-size: 9px;
            margin-top: 8px;
            border-top: 1px dashed #000;
            padding-top: 4px;
        }

        .tax-row {
            display: flex;
            justify-content: space-between;
        }

        .footer-legal {
            font-size: 8px;
            margin-top: 10px;
            text-align: center;
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
        {{-- HEADER (Store Info) --}}
        <div class="text-center mb-1">
            <div class="fw-bold" style="font-size: 14px; text-transform: uppercase;">{{ $storeName }}</div>
            @if($storeOwner)
                <div style="font-size: 10px;">Prop. {{ $storeOwner }}</div>
            @endif
            <div>{{ $storeAddress }}</div>
            <div>TIN: {{ $tin }} ({{ ($taxType == 'non_vat') ? 'Non-VAT' : 'VAT' }})</div>
        </div>

        <div class="text-center border-bottom mb-1">
            <div class="fw-bold" style="font-size: 14px; margin-top: 5px;">SALES INVOICE</div>
        </div>

        {{-- TRANSACTION DETAILS --}}
        <div class="border-bottom" style="font-size: 10px;">
            <div class="d-flex justify-content-between">
                <span>SI No:</span><span class="fw-bold">{{ $sale->invoice_number }}</span>
            </div>
            <div>Date: {{ $sale->created_at->format('M d, Y h:i A') }}</div>
            <div>Cashier: {{ $sale->user->name }}</div>

            @if($sale->customer)
                <div style="margin-top: 4px; border-top: 1px dotted #ccc; padding-top: 2px;">
                    <div>Sold To: {{ $sale->customer->name }}</div>
                    <div>Address: {{ $sale->customer->address ?? 'N/A' }}</div>
                    <div>TIN: {{ $sale->customer->tin ?? '000-000-000-000' }}</div>
                    <div>Bus. Style: {{ $sale->customer->business_style ?? 'N/A' }}</div>
                </div>
            @else
                <div style="margin-top: 4px;">Sold To: Walk-in Customer</div>
            @endif
        </div>

        {{-- ITEMS --}}
        <table class="table mb-1">
            <thead>
                <tr style="border-bottom: 1px solid #000;">
                    <th style="text-align:left">Qty</th>
                    <th style="text-align:left">Item</th>
                    <th style="text-align:right">Amt</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $item)
                    @php
                        // Determine Tax Marker
                        $marker = '(V)'; // Default
                        if (isset($item->product->tax_type)) {
                            switch ($item->product->tax_type) {
                                case 'vat_exempt':
                                    $marker = '(E)';
                                    break;
                                case 'zero_rated':
                                    $marker = '(Z)';
                                    break;
                                default:
                                    $marker = '(V)';
                            }
                        }
                    @endphp
                    <tr>
                        <td class="qty">{{ $item->quantity }}</td>
                        <td>{{ $item->product->name }} {{ $marker }}</td>
                        <td class="price">{{ number_format($item->quantity * $item->price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- TOTALS --}}
        <div class="border-bottom mb-1">
            @if($sale->points_discount > 0)
                <div class="tax-row">
                    <span>Subtotal</span><span>{{ number_format($sale->total_amount + $sale->points_discount, 2) }}</span>
                </div>
                <div class="tax-row"><span>Pts Discount</span><span>-{{ number_format($sale->points_discount, 2) }}</span>
                </div>
            @endif

            @if($sale->discount_amount > 0)
                {{-- Display Subtotal before discount if strictly needed, but usually we just show the deduciton --}}
                {{-- Calculate 'Gross' if we want to show it? Or just show the discount line. --}}
                {{-- Let's just show the discount line for clarity --}}
                <div class="tax-row">
                    <span>{{ strtoupper($sale->discount_type ?? 'DISCOUNT') }} (20%)</span>
                    <span>-{{ number_format($sale->discount_amount, 2) }}</span>
                </div>
            @endif

            <div class="tax-row fw-bold" style="font-size: 14px; margin-top: 5px;">
                <span>TOTAL AMOUNT</span>
                <span>₱{{ number_format($sale->total_amount, 2) }}</span>
            </div>
        </div>

        {{-- SC/PWD DETAILS --}}
        @if($sale->discount_amount > 0 && in_array($sale->discount_type, ['sc', 'pwd']))
            <div class="mb-1" style="font-size: 9px; border-bottom: 1px dashed #000; padding-bottom: 4px;">
                <div class="fw-bold" style="text-transform: uppercase;">
                    {{ $sale->discount_type == 'sc' ? 'Senior Citizen' : 'PWD' }} Details
                </div>
                <div>Name: {{ $sale->discount_name }}</div>
                <div>ID No: {{ $sale->discount_card_no }}</div>
                <div style="margin-top: 15px; border-top: 1px solid #000; width: 60%; text-align: center;">Signature</div>
            </div>
        @endif

        {{-- TAX BREAKDOWN --}}
        @php
            $vatable = $sale->vatable_sales;
            $vatAmount = $sale->vat_amount; // Corrected column name
            $vatExempt = $sale->vat_exempt_sales;
            $zeroRated = $sale->vat_zero_rated_sales;

            // Recalculate if not saved in older records (Shim)
            if ($vatable == 0 && $taxType != 'non_vat' && $sale->total_amount > 0 && $vatExempt == 0) {
                $rawTotal = $sale->total_amount;
                $vatable = $rawTotal / 1.12;
                $vatAmount = $rawTotal - $vatable;
            }
        @endphp

        <div class="tax-breakdown">
            @if($taxType == 'non_vat')
                <div class="tax-row"><span>Vat Exempt Sales</span><span>{{ number_format($sale->total_amount, 2) }}</span>
                </div>
            @else
                <div class="tax-row"><span>Vatable Sales</span><span>{{ number_format($vatable, 2) }}</span></div>
                <div class="tax-row"><span>VAT Amount (12%)</span><span>{{ number_format($vatAmount, 2) }}</span></div>
                <div class="tax-row"><span>VAT Exempt Sales</span><span>{{ number_format($vatExempt, 2) }}</span></div>
                <div class="tax-row"><span>Zero Rated Sales</span><span>{{ number_format($zeroRated, 2) }}</span></div>
            @endif
        </div>

        {{-- PAYMENT INFO --}}
        <div class="mb-1" style="font-size: 10px; margin-top: 5px; border-top: 1px dashed #000; padding-top: 4px;">
            <div class="tax-row"><span>Payment Type:</span><span>{{ ucfirst($sale->payment_method) }}</span></div>
            @if($sale->payment_method == 'cash')
                <div class="tax-row"><span>Cash Keyed:</span><span>{{ number_format($sale->amount_paid, 2) }}</span></div>
                <div class="tax-row">
                    <span>Change:</span><span>{{ number_format($sale->amount_paid - $sale->total_amount, 2) }}</span>
                </div>
            @endif
        </div>

        {{-- FOOTER LEGAL --}}
        <div class="footer-legal">
            <div>Serial No: {{ $serialNumber }}</div>
            <div>MIN: {{ $minNumber }}</div>
            <div>Permit No: {{ $permit }}</div>
            <div style="margin-top: 5px;">
                "THIS INVOICE SHALL BE VALID FOR FIVE (5) YEARS FROM THE DATE OF THE PERMIT TO USE."
            </div>
            <div style="margin-top: 5px; font-style: italic;">
                {{ $receiptFooter }}
            </div>
            <div style="margin-top: 5px; font-weight: bold;">
                Developer: Aljayvee Versola / VERAPOS
            </div>
        </div>

        <button onclick="window.print()" class="no-print"
            style="width: 100%; padding: 10px; margin-top: 15px; cursor: pointer; background: #000; color: white; border: none; font-weight: bold;">
            PRINT INVOICE
        </button>
    </div>

</body>

</html>