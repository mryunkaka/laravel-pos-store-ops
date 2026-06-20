<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Barcode {{ $product->code }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; color: #111; }
        .toolbar { text-align: center; margin: 16px 0; }
        .btn { border: 0; background: #2563eb; color: #fff; padding: 8px 14px; border-radius: 4px; cursor: pointer; }
        .label {
            width: 58mm;
            min-height: 34mm;
            margin: 0 auto;
            background: #fff;
            padding: 4mm;
            text-align: center;
            border: 1px solid #ddd;
        }
        .name { font-size: 11px; font-weight: bold; margin-bottom: 2mm; }
        .price { font-size: 11px; margin-top: 2mm; }
        .code { font-size: 10px; margin-top: 1mm; letter-spacing: 1px; }
        svg, .label div[style] { max-width: 100%; }
        @media print {
            @page { size: 58mm 34mm; margin: 0; }
            body { background: #fff; margin: 0; }
            .toolbar { display: none; }
            .label { border: 0; margin: 0; width: 58mm; min-height: 34mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn" onclick="window.print()">Cetak Label</button>
    </div>
    <div class="label">
        <div class="name">{{ $product->name }}</div>
        {!! $barcode !!}
        <div class="code">{{ $product->code }}</div>
        <div class="price">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</div>
    </div>
    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
