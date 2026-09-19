<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->invoice_no }}</title>
    <style>
        @page { margin: 14mm; }
        * { box-sizing: border-box; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.45; }
        h1, h2, h3, p { margin: 0; }
        .header { border-bottom: 2px solid #1f2937; padding-bottom: 12px; }
        .header-table, .meta-table, .summary-table, .items { width: 100%; border-collapse: collapse; }
        .logo { max-width: 115px; max-height: 55px; }
        .store-name { font-size: 18px; font-weight: bold; margin-bottom: 3px; }
        .muted { color: #6b7280; }
        .invoice-title { color: #111827; font-size: 20px; font-weight: bold; text-align: right; }
        .invoice-number { font-size: 12px; text-align: right; margin-top: 4px; }
        .section { margin-top: 18px; }
        .section-title { border-bottom: 1px solid #d1d5db; color: #374151; font-size: 11px; font-weight: bold; margin-bottom: 7px; padding-bottom: 4px; text-transform: uppercase; }
        .meta-table td { padding: 2px 0; vertical-align: top; width: 50%; }
        .meta-label { color: #6b7280; display: inline-block; width: 78px; }
        .items th { background: #f3f4f6; border-bottom: 1px solid #9ca3af; color: #374151; font-size: 9px; padding: 7px 5px; text-align: left; }
        .items td { border-bottom: 1px solid #e5e7eb; padding: 7px 5px; vertical-align: top; }
        .items .number { text-align: right; white-space: nowrap; }
        .product-name { font-weight: bold; }
        .product-note { color: #6b7280; font-size: 8px; margin-top: 2px; }
        .summary-wrap { margin-left: 48%; }
        .summary-table td { border-bottom: 1px solid #e5e7eb; padding: 5px 0; }
        .summary-table td:last-child { text-align: right; white-space: nowrap; }
        .summary-table .grand-total td { border-bottom: 2px solid #1f2937; border-top: 2px solid #1f2937; font-size: 13px; font-weight: bold; padding: 8px 0; }
        .status { font-weight: bold; }
        .footer { border-top: 1px solid #d1d5db; color: #6b7280; margin-top: 24px; padding-top: 10px; text-align: center; }
    </style>
</head>
<body>
    <table class="header-table header">
        <tr>
            <td style="width: 62%; vertical-align: top;">
                @if($logoDataUri)
                    <img class="logo" src="{{ $logoDataUri }}" alt="{{ $setting->store_name }}">
                @endif
                <div class="store-name">{{ $setting->store_name ?: 'POS Shop' }}</div>
                @if($setting->address)<div class="muted">{{ $setting->address }}</div>@endif
                @if($setting->phone)<div class="muted">{{ $setting->phone }}</div>@endif
            </td>
            <td style="vertical-align: top;">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">{{ $order->invoice_no }}</div>
                <div class="muted" style="text-align: right;">{{ $order->order_date?->locale('id')->translatedFormat('l, d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Informasi Transaksi</div>
        <table class="meta-table">
            <tr>
                <td><span class="meta-label">Pelanggan</span>{{ $order->customer?->name ?: '-' }}</td>
                <td><span class="meta-label">Kasir</span>{{ $order->cashier?->name ?: '-' }}</td>
            </tr>
            <tr>
                <td><span class="meta-label">Nomor</span>{{ $order->customer?->phone ?: '-' }}</td>
                <td><span class="meta-label">Status</span><span class="status">{{ $order->due_amount <= 0 ? 'LUNAS' : 'BELUM LUNAS' }}</span></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Detail Produk</div>
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th>Produk</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 18%;">Harga</th>
                    <th style="width: 20%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->details as $detail)
                    @php($product = $detail->product)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="product-name">{{ $product?->name ?: 'Produk' }}</div>
                            @if($product?->material)<div class="product-note">Bahan: {{ $product->material }}</div>@endif
                            @if($product?->print_size)<div class="product-note">Ukuran: {{ $product->print_size }}</div>@endif
                            @if($product?->print_notes)<div class="product-note">Keterangan: {{ $product->print_notes }}</div>@endif
                        </td>
                        <td class="number">{{ $detail->quantity }}</td>
                        <td class="number">Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                        <td class="number">Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section summary-wrap">
        <table class="summary-table">
            <tr><td>Subtotal</td><td>Rp {{ number_format($order->sub_total, 0, ',', '.') }}</td></tr>
            <tr><td>Diskon</td><td>Rp {{ number_format($order->discount, 0, ',', '.') }}</td></tr>
            <tr><td>Pajak/PPN</td><td>Rp {{ number_format($order->tax_total ?? $order->vat, 0, ',', '.') }}</td></tr>
            <tr><td>Biaya lainnya</td><td>Rp {{ number_format($order->service_charge, 0, ',', '.') }}</td></tr>
            <tr class="grand-total"><td>Total</td><td>Rp {{ number_format($order->total, 0, ',', '.') }}</td></tr>
            <tr><td>Metode pembayaran</td><td>{{ $order->paymentHistoryText() }}</td></tr>
            <tr><td>Jumlah dibayar</td><td>Rp {{ number_format($order->pay_amount, 0, ',', '.') }}</td></tr>
            <tr><td>{{ $order->due_amount > 0 ? 'Sisa/piutang' : 'Kembalian' }}</td><td>Rp {{ number_format($order->due_amount > 0 ? $order->due_amount : abs(min($order->due_amount, 0)), 0, ',', '.') }}</td></tr>
        </table>
    </div>

    <div class="footer">
        Terima kasih atas pembelian Anda.<br>
        Invoice elektronik dari {{ $setting->store_name ?: 'POS Shop' }}.
    </div>
</body>
</html>
