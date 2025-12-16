@php
    use Illuminate\Support\Facades\Crypt;
    use Illuminate\Contracts\Encryption\DecryptException;

    // 1. Fetch Store Details
    $storeName = \App\Models\Setting::where('key', 'store_name')->value('value') ?? 'Sari-Sari Store';
    $storeAddress = \App\Models\Setting::where('key', 'store_address')->value('value') ?? '';
    $storeContact = \App\Models\Setting::where('key', 'store_contact')->value('value') ?? '';
    $receiptFooter = \App\Models\Setting::where('key', 'receipt_footer')->value('value') ?? 'Thank you!';
    
    // 2. Fetch & Decrypt Tax Settings
    $enableTax = \App\Models\Setting::where('key', 'enable_tax')->value('value') ?? '0';
    
    // Decrypt TIN
    $rawTin = \App\Models\Setting::where('key', 'store_tin')->value('value');
    try {
        $tin = $rawTin ? Crypt::decryptString($rawTin) : '';
    } catch (DecryptException $e) {
        $tin = $rawTin; // Fallback
    }

    // Decrypt Permit
    $rawPermit = \App\Models\Setting::where('key', 'business_permit')->value('value');
    try {
        $permit = $rawPermit ? Crypt::decryptString($rawPermit) : '';
    } catch (DecryptException $e) {
        $permit = $rawPermit; // Fallback
    }
    
    // Tax Calculation Variables
    $taxRate = (float) (\App\Models\Setting::where('key', 'tax_rate')->value('value') ?? 12);
    $taxType = \App\Models\Setting::where('key', 'tax_type')->value('value') ?? 'inclusive'; 

    $totalAmount = $sale->total_amount;
    $vatableSales = 0;
    $vatAmount = 0;
    $vatExempt = 0;

    // 3. Perform Calculation
    if ($taxType === 'non_vat') {
        $vatExempt = $totalAmount;
    } else {
        $vatableSales = $totalAmount / (1 + ($taxRate / 100));
        $vatAmount = $totalAmount - $vatableSales;
    }
@endphp

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
        .border-bottom { border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 5px; }
        .table { width: 100%; border-collapse: collapse; }
        .table td { vertical-align: top; }
        .qty { width: 30px; }
        .price { width: 60px; text-align: right; }
        .tax-breakdown { font-size: 10px; color: #333; margin-bottom: 10px; border-bottom: 1px dashed #ccc; padding-bottom: 5px; }
        .tax-row { display: flex; justify-content: space-between; }
        
        @media print {
            body { background: none; padding: 0; }
            .receipt { box-shadow: none; width: 100%; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="receipt">
        {{-- HEADER --}}
        <div class="text-center mb-1">
            <div class="fw-bold" style="font-size: 16px;">{{ $storeName }}</div>
            <div>{{ $storeAddress }}</div>
            <div>{{ $storeContact }}</div>
            
            {{-- TAX INFO (Only if Enabled) --}}
            @if($enableTax == '1')
                <div style="margin-top: 5px; font-size: 10px;">
                    @if($taxType === 'non_vat')
                        <div><strong>NON-VAT REG. TIN: {{ $tin }}</strong></div>
                    @else
                        <div><strong>VAT REG. TIN: {{ $tin }}</strong></div>
                    @endif
                    @if($permit) <div>Permit #: {{ $permit }}</div> @endif
                </div>
            @endif
        </div>

        {{-- TRANSACTION DETAILS --}}
        <div class="border-bottom" style="font-size: 11px;">
            <div>Date: {{ $sale->created_at->format('M d, Y h:i A') }}</div>
            <div>OR #: {{ str_pad($sale->id, 8, '0', STR_PAD_LEFT) }}</div>
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

        {{-- TAX BREAKDOWN (Dynamic based on Settings) --}}
        @if($enableTax == '1')
        <div class="tax-breakdown">
            @if($taxType === 'non_vat')
                <div class="tax-row"><span>Total Sales (VAT Exempt)</span><span>{{ number_format($vatExempt, 2) }}</span></div>
            @else
                <div class="tax-row"><span>Vatable Sales</span><span>{{ number_format($vatableSales, 2) }}</span></div>
                <div class="tax-row"><span>VAT Amount ({{ $taxRate }}%)</span><span>{{ number_format($vatAmount, 2) }}</span></div>
                <div class="tax-row"><span>VAT Exempt</span><span>0.00</span></div>
                <div class="tax-row"><span>Zero Rated</span><span>0.00</span></div>
            @endif
        </div>
        @endif

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
            @if($enableTax == '1')
                <div style="font-size: 9px; margin-top: 5px;">THIS DOCUMENT IS NOT VALID FOR CLAIM OF INPUT TAX</div>
                {{-- Standard PH disclaimer for non-official receipts --}}
            @endif
        </div>

        <button onclick="window.print()" class="no-print" style="width: 100%; padding: 10px; margin-top: 15px; cursor: pointer; background: #000; color: white; border: none; font-weight: bold;">
            PRINT RECEIPT
        </button>
    </div>

</body>
</html>