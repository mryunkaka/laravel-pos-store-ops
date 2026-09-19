<!DOCTYPE html>
<html lang="id">

<head>
    <title>POS Faktur #{{ $order->invoice_no }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">

    <!-- External CSS libraries -->
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/css/bootstrap.min.css') }}">

    <!-- Google fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Custom Stylesheet -->
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/css/style.css') }}">
</head>

<body>
    <div class="invoice-16 invoice-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="invoice-inner-9" id="invoice_wrapper">
                        <!-- Invoice Header -->
                        <div class="invoice-top">
                            <div class="row">
                                <div class="col-lg-6 col-sm-6">
                                    <div class="logo">
                                        <img class="logo" src="{{ $setting->logo ? asset('storage/' . $setting->logo) : asset('assets/images/logo.png') }}" alt="{{ $setting->store_name ?: 'POS Shop' }}">
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6">
                                    <div class="invoice">
                                        <h1>#<span>{{ $order->invoice_no }}</span></h1>
                                    </div>
                                </div>
                            </div>
                        </div>
<!-- Invoice Info -->
                        <div class="invoice-info">
                            <div class="row">
                                <div class="col-sm-6 mb-50">
                                    <div class="invoice-number">
                                        <h4 class="inv-title-1">Tanggal invoice:</h4>
                                        <p class="invo-addr-1">
                                            {{ $order->order_date->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-sm-6 text-end mb-50">
                                    <h4 class="inv-title-1">{{ $setting->store_name ?: 'POS Shop' }}</h4>
                                    @if ($setting->phone)
                                        <p class="inv-from-1">{{ $setting->phone }}</p>
                                    @endif
                                    @if ($setting->address)
                                        <p class="inv-from-2"><a href="{{ $setting->google_maps_url ?: '#' }}" target="_blank" rel="noopener noreferrer">{{ $setting->address }}</a></p>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 mb-50">
                                    <h4 class="inv-title-1">Pelanggan</h4>
                                    <p class="inv-from-1">{{ $order->customer->name }}</p>
                                    <p class="inv-from-1">{{ $order->customer->email }}</p>
                                    <p class="inv-from-1">{{ $order->customer->phone }}</p>
                                    <p class="inv-from-2">{{ $order->customer->address }}</p>
                                </div>
                                <div class="col-sm-6 text-end mb-50">
                                    <h4 class="inv-title-1">Detail</h4>
                                    <p class="inv-from-1">Tipe Pembayaran: {{ $order->payment_type }}</p>
                                    <p class="inv-from-1">Total Dibayar: {{ format_rupiah($order->pay_amount) }}</p>
                                    <p class="inv-from-1">{{ $order->due_amount > 0 ? 'Sisa Piutang' : 'Kembalian' }}: {{ format_rupiah($order->due_amount > 0 ? $order->outstandingAmount() : $order->changeAmount()) }}</p>
                                </div>
                            </div>
                        </div>
<!-- Order Summary -->
                        <div class="order-summary">
                            <div class="table-outer">
                                <table class="default-table invoice-table">
                                    <thead>
                                        <tr>
                                            <th>Deskripsi</th>
                                            <th>Harga</th>
                                            <th>Jumlah</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($orderDetails as $item)
                                            <tr>
                                                <td>{{ $item->product->name }}</td>
                                                <td>{{ format_rupiah($item->unit_price) }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>{{ format_rupiah($item->total) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td colspan="3" class="text-right">Subtotal</td>
                                            <td>{{ format_rupiah($order->sub_total) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right">Diskon</td>
                                            <td>-{{ format_rupiah($order->discountTotal()) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right">Pajak/PPN</td>
                                            <td>{{ format_rupiah($order->taxAmount()) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right">Biaya lainnya</td>
                                            <td>{{ format_rupiah($order->service_charge) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right"><strong class="text-danger">Total</strong></td>
                                            <td><strong class="text-danger">{{ format_rupiah($order->total) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right">Metode pembayaran</td>
                                            <td>{{ $order->paymentHistoryText() }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right">Total dibayar</td>
                                            <td>{{ format_rupiah($order->pay_amount) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right">{{ $order->due_amount > 0 ? 'Sisa Piutang' : 'Kembalian' }}</td>
                                            <td>{{ format_rupiah($order->due_amount > 0 ? $order->outstandingAmount() : $order->changeAmount()) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="invoice-btn-section clearfix d-print-none">
                        <a href="javascript:window.print()" class="btn btn-lg btn-print">
                            Cetak Faktur
                        </a>
                        <a id="invoice_download_btn" class="btn btn-lg btn-download">
                            Unduh Faktur
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/invoice/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/invoice/js/jspdf.min.js') }}"></script>
    <script src="{{ asset('assets/invoice/js/html2canvas.js') }}"></script>
    <script src="{{ asset('assets/invoice/js/app.js') }}"></script>
</body>

</html>
