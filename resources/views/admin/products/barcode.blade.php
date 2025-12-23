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
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .controls {
            background: linear-gradient(135deg, #1f2937, #111827);
            padding: 15px 30px;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 20px;
            border-radius: 50px;
            z-index: 1000;
        }
        .btn {
            border: none;
            padding: 10px 24px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .btn-primary { background: #3b82f6; color: white; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5); }
        .btn-primary:hover { background: #2563eb; transform: translateY(-1px); }
        .btn-secondary { background: #4b5563; color: white; box-shadow: 0 4px 6px -1px rgba(75, 85, 99, 0.5); }
        .btn-secondary:hover { background: #374151; transform: translateY(-1px); }

        .sticker-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .sticker {
            background: white;
            border: 2px dashed #d1d5db;
            width: 350px;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
        }
        .sticker:hover { transform: translateY(-5px); border-color: #3b82f6; }

        .store-name { font-size: 14px; text-transform: uppercase; margin-bottom: 10px; color: #6b7280; letter-spacing: 2px; font-weight: 700; }
        .product-name { font-weight: 800; font-size: 20px; margin: 10px 0; color: #111827; line-height: 1.2; }
        .product-price { font-size: 28px; font-weight: 900; margin-top: 10px; color: #3b82f6; }
        
        @media print {
            body { background: white; padding: 0; min-height: auto; display: block; }
            .no-print { display: none !important; }
            .sticker { border: none; box-shadow: none; margin: 0; width: 100%; padding: 0; transform: none !important; text-align: center; } 
            .sticker-container { display: block; height: auto; }
            .store-name { font-size: 12px; color: black; }
            .product-name { font-size: 18px; color: black; }
            .product-price { font-size: 24px; color: black; }
        }
    </style>
</head>
<body onload="setTimeout(window.print, 500)">

    <div class="controls no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Label
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

    <div class="sticker-container">
        <div class="sticker">
            {{-- Fetch Store Name Dynamically --}}
            <div class="store-name">
                {{ \App\Models\Setting::where('key', 'store_name')->value('value') ?? config('app.name') }}
            </div>
            
            <svg id="barcode"></svg>
    
            <div class="product-name">{{ $product->name }}</div>
            <div class="product-price">₱{{ number_format($product->price, 2) }}</div>
        </div>
    </div>

    <script>
        JsBarcode("#barcode", "{{ $product->sku }}", {
            format: "CODE128",
            lineColor: "#111827",
            width: 2.5,
            height: 70,
            displayValue: true,
            fontSize: 14,
            fontOptions: "bold",
            margin: 10
        });
    </script>
</body>
</html>
