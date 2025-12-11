<!DOCTYPE html>
<html>
<head>
    <title>Barcode: {{ $product->name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        body {
            font-family: sans-serif;
            text-align: center;
            margin: 0;
            padding: 20px;
        }
        .sticker {
            border: 1px dashed #333;
            width: 300px;
            padding: 15px;
            margin: 0 auto 20px auto;
            display: block;
            page-break-inside: avoid;
        }
        .store-name { font-size: 12px; text-transform: uppercase; margin-bottom: 5px; }
        .product-name { font-weight: bold; font-size: 16px; margin: 5px 0; }
        .product-price { font-size: 18px; font-weight: bold; margin-top: 5px; }
        
        @media print {
            .no-print { display: none; }
            .sticker { border: none; } 
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; font-weight: bold;">Print Label</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">Close</button>
        <p><small>Ensure your printer settings match your sticker paper.</small></p>
    </div>

    <div class="sticker">
        {{-- Fetch Store Name Dynamically --}}
        <div class="store-name">
            {{ \App\Models\Setting::where('key', 'store_name')->value('value') ?? config('app.name') }}
        </div>
        
        <svg id="barcode"></svg>

        <div class="product-name">{{ $product->name }}</div>
        <div class="product-price">₱{{ number_format($product->price, 2) }}</div>
    </div>

    <script>
        JsBarcode("#barcode", "{{ $product->sku }}", {
            format: "CODE128",
            lineColor: "#000",
            width: 2,
            height: 50,
            displayValue: true
        });
    </script>

</body>
</html>