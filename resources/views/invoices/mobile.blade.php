<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->invoice_no }}</title>
    <style>
        body { margin: 0; background: #f3f5f7; color: #1f2937; font-family: Arial, sans-serif; }
        .page { max-width: 520px; margin: 0 auto; background: #fff; min-height: 100vh; }
        .hero { background: #111827; color: #fff; padding: 24px 20px; }
        .hero h1 { margin: 0 0 6px; font-size: 22px; }
        .hero p { margin: 0; color: #d1d5db; font-size: 13px; }
        .section { padding: 18px 20px; border-bottom: 1px solid #e5e7eb; }
        .row { display: flex; justify-content: space-between; gap: 16px; margin: 8px 0; }
        .label { color: #6b7280; }
        .value { font-weight: 700; text-align: right; }
        .item { padding: 14px 0; border-bottom: 1px dashed #d1d5db; }
        .item:last-child { border-bottom: 0; }
        .item h3 { margin: 0 0 8px; font-size: 16px; }
        .status { display: inline-block; padding: 6px 10px; border-radius: 4px; font-size: 12px; font-weight: 700; }
        .paid { background: #dcfce7; color: #166534; }
        .due { background: #fef3c7; color: #92400e; }
        .total { background: #f9fafb; }
        .footer { padding: 18px 20px 28px; color: #6b7280; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
    <main class="page">
        <header class="hero">
            <h1>{{ $setting->store_name }}</h1>
            <p>{{ $setting->address }}</p>
            <p>{{ $setting->phone }}</p>
        </header>

        <section class="section">
            <div class="row"><span class="label">Invoice</span><span class="value">{{ $order->invoice_no }}</span></div>
            <div class="row"><span class="label">Tanggal</span><span class="value">{{ $order->created_at->format('d/m/Y H:i') }}</span></div>
            <div class="row"><span class="label">Pelanggan</span><span class="value">{{ $order->customer->name ?? '-' }}</span></div>
            <div class="row">
                <span class="label">Status</span>
                <span class="status {{ $order->due_amount <= 0 ? 'paid' : 'due' }}">{{ $order->due_amount <= 0 ? 'LUNAS' : 'BELUM LUNAS' }}</span>
            </div>
        </section>

        <section class="section">
            @foreach($order->details as $detail)
                @php($product = $detail->product)
                <div class="item">
                    <h3>{{ $product->name ?? 'Produk' }}</h3>
                    <div class="row"><span class="label">Bahan</span><span class="value">{{ $product->material ?: ($product->category->name ?? '-') }}</span></div>
                    <div class="row"><span class="label">Ukuran</span><span class="value">{{ $product->print_size ?: '-' }}</span></div>
                    <div class="row"><span class="label">Keterangan</span><span class="value">{{ $product->print_notes ?: '-' }}</span></div>
                    <div class="row"><span class="label">Qty</span><span class="value">{{ $detail->quantity }}</span></div>
                    <div class="row"><span class="label">Subtotal</span><span class="value">Rp {{ number_format($detail->total, 0, ',', '.') }}</span></div>
                </div>
            @endforeach
        </section>

        <section class="section total">
            <div class="row"><span class="label">Total Order</span><span class="value">Rp {{ number_format($order->total, 0, ',', '.') }}</span></div>
            <div class="row"><span class="label">Total Bayar</span><span class="value">Rp {{ number_format($order->pay_amount, 0, ',', '.') }}</span></div>
            <div class="row"><span class="label">Sisa Pembayaran</span><span class="value">Rp {{ number_format(max($order->due_amount, 0), 0, ',', '.') }}</span></div>
        </section>

        @if($setting->whatsapp_payment_instructions)
            <section class="section">
                <pre style="white-space: pre-wrap; font-family: inherit; margin: 0;">{{ $setting->whatsapp_payment_instructions }}</pre>
            </section>
        @endif

        <footer class="footer">
            Invoice digital otomatis dari {{ $setting->store_name }}.
        </footer>
    </main>
</body>
</html>
