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
            @if ($setting->logo)
                <img src="{{ asset('storage/' . $setting->logo) }}" alt="{{ $setting->store_name }}" style="max-width: 180px; max-height: 70px; object-fit: contain;">
            @endif
            <h1>{{ $setting->store_name ?: 'POS Shop' }}</h1>
            @if ($setting->address)
                <p><a href="{{ $setting->google_maps_url ?: '#' }}" target="_blank" rel="noopener noreferrer" style="color: inherit;">{{ $setting->address }}</a></p>
            @endif
            @if ($setting->phone)
                <p>{{ $setting->phone }}</p>
            @endif
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
                    <div class="row"><span class="label">Bahan</span><span class="value">{{ $product->material ?: ($product->category_name ?? optional($product->category)->name ?? '-') }}</span></div>
                    <div class="row"><span class="label">Ukuran</span><span class="value">{{ $product->print_size ?: '-' }}</span></div>
                    <div class="row"><span class="label">Keterangan</span><span class="value">{{ $product->print_notes ?: '-' }}</span></div>
                    <div class="row"><span class="label">Qty</span><span class="value">{{ $detail->quantity }}</span></div>
                    <div class="row"><span class="label">Subtotal</span><span class="value">{{ format_rupiah($detail->total) }}</span></div>
                </div>
            @endforeach
        </section>

        <section class="section total">
            <div class="row"><span class="label">Subtotal</span><span class="value">{{ format_rupiah($order->sub_total) }}</span></div>
            <div class="row"><span class="label">Diskon</span><span class="value">-{{ format_rupiah($order->discountTotal()) }}</span></div>
            <div class="row"><span class="label">Pajak/PPN</span><span class="value">{{ format_rupiah($order->taxAmount()) }}</span></div>
            <div class="row"><span class="label">Biaya lainnya</span><span class="value">{{ format_rupiah($order->service_charge) }}</span></div>
            <div class="row"><span class="label">Total Order</span><span class="value">{{ format_rupiah($order->total) }}</span></div>
            <div class="row"><span class="label">Metode pembayaran</span><span class="value">{{ $order->paymentHistoryText() }}</span></div>
            <div class="row"><span class="label">Total Bayar</span><span class="value">{{ format_rupiah($order->pay_amount) }}</span></div>
            <div class="row"><span class="label">{{ $order->due_amount > 0 ? 'Sisa Piutang' : 'Kembalian' }}</span><span class="value">{{ format_rupiah($order->due_amount > 0 ? $order->outstandingAmount() : $order->changeAmount()) }}</span></div>
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
