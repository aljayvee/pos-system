<!DOCTYPE html>
<html>
<head>
    <title>Barcode: {{ $product->name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
        }
        .controls {
            background: #1f2937;
            padding: 15px;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }
        .btn {
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            font-family: inherit;
        }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-primary:hover { background: #2563eb; }
        .btn-secondary { background: #4b5563; color: white; }
        .btn-secondary:hover { background: #374151; }

        .sticker {
            background: white;
            border: 1px dashed #d1d5db;
            width: 300px;
            padding: 20px;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .store-name { font-size: 12px; text-transform: uppercase; margin-bottom: 5px; color: #6b7280; letter-spacing: 1px; }
        .product-name { font-weight: 700; font-size: 16px; margin: 5px 0; color: #111827; }
        .product-price { font-size: 20px; font-weight: 800; margin-top: 5px; color: #111827; }
        
        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none !important; }
            .sticker { border: none; box-shadow: none; margin: 0; width: auto; padding: 0; } 
            .sticker { page-break-inside: avoid; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="controls no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Label
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="fas fa-times"></i> Close Window
        </button>
        <span style="font-size: 0.9em; opacity: 0.8; margin-left: 10px;">
            <i class="fas fa-info-circle"></i> Ensure layout is set to "Portrait"
        </span>
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
            displayValue: true,
            margin: 5
        });
    </script>

</body>
</html>