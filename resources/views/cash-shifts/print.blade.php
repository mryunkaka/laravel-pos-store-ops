<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Shift Kasir #{{ $shift->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 5px 0; }
        .info-table { width: 100%; margin-bottom: 15px; }
        .info-table td { padding: 3px 0; vertical-align: top; }
        .summary-box { border: 1px solid #000; padding: 10px; margin-bottom: 15px; }
        .summary-row { display: flex; justify-content: space-between; margin: 5px 0; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data th, table.data td { border: 1px solid #000; padding: 5px; text-align: left; }
        table.data th { background: #f0f0f0; }
        .text-right { text-align: right; }
        .signature { margin-top: 30px; display: flex; justify-content: space-between; }
        .signature-box { text-align: center; width: 45%; }
        .signature-line { margin-top: 50px; border-top: 1px solid #000; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN SHIFT KASIR</h2>
        <p>Shift #{{ $shift->id }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="30%"><strong>Kasir:</strong></td>
            <td>{{ $shift->user->name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Tanggal:</strong></td>
            <td>{{ $shift->start_time->format('d M Y') }}</td>
        </tr>
        <tr>
            <td><strong>Jam Buka:</strong></td>
            <td>{{ $shift->start_time->format('H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Jam Tutup:</strong></td>
            <td>{{ $shift->end_time ? $shift->end_time->format('H:i') : 'Masih Buka' }}</td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td>{{ $shift->status == 'active' ? 'Aktif' : 'Tutup' }}</td>
        </tr>
    </table>

    <div class="summary-box">
        <div class="summary-row">
            <strong>Kas Awal:</strong>
            <strong>Rp {{ number_format($shift->opening_balance, 0, ',', '.') }}</strong>
        </div>
        <div class="summary-row">
            <strong>Total Penjualan:</strong>
            <strong>Rp {{ number_format($shift->total_sales, 0, ',', '.') }}</strong>
        </div>
        <div class="summary-row">
            <strong>Total Tunai:</strong>
            <strong>Rp {{ number_format($shift->total_cash, 0, ',', '.') }}</strong>
        </div>
        <div class="summary-row">
            <strong>Total Non-Tunai:</strong>
            <strong>Rp {{ number_format($shift->total_non_cash, 0, ',', '.') }}</strong>
        </div>
        <div class="summary-row">
            <strong>Total Void:</strong>
            <strong>Rp {{ number_format($shift->total_void, 0, ',', '.') }}</strong>
        </div>
        <div class="summary-row">
            <strong>Total Refund:</strong>
            <strong>Rp {{ number_format($shift->total_refund, 0, ',', '.') }}</strong>
        </div>
        <hr>
        <div class="summary-row">
            <strong>Kas Akhir:</strong>
            <strong>Rp {{ number_format($shift->closing_balance, 0, ',', '.') }}</strong>
        </div>
    </div>

    <h3>Rincian Transaksi</h3>
    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Waktu</th>
                <th>Tipe</th>
                <th>Detail</th>
                <th>Pembayaran</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shift->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->transaction_time->format('H:i') }}</td>
                    <td>{{ $detail->transaction_type }}</td>
                    <td>{{ $detail->order ? 'Order #'.substr($detail->order->invoice_no, -6) : $detail->description }}</td>
                    <td>{{ $detail->payment_type }}</td>
                    <td class="text-right">Rp {{ number_format($detail->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada transaksi</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Total</th>
                <th class="text-right">Rp {{ number_format($shift->details->sum('amount'), 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    @if($shift->closing_notes)
        <div style="margin-top: 15px;">
            <strong>Catatan:</strong>
            <p>{{ $shift->closing_notes }}</p>
        </div>
    @endif

    <div class="signature">
        <div class="signature-box">
            <strong>Kasir</strong>
            <div class="signature-line">{{ $shift->user->name ?? '-' }}</div>
        </div>
        @if($shift->approvedBy)
        <div class="signature-box">
            <strong>Supervisor</strong>
            <div class="signature-line">{{ $shift->approvedBy->name }}</div>
        </div>
        @endif
    </div>
</body>
</html>