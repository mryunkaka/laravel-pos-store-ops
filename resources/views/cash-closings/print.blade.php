<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Tutup Kasir {{ $closing->closing_date }}</title>
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
        <h2>LAPORAN TUTUP KASIR HARIAN</h2>
        <p>{{ $closing->closing_date->format('d F Y') }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="30%"><strong>Tanggal Tutup:</strong></td>
            <td>{{ $closing->closing_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <td><strong>Waktu Tutup:</strong></td>
            <td>{{ $closing->closing_time->format('H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td>{{ $closing->status == 'verified' ? 'Verified' : 'Tutup' }}</td>
        </tr>
        @if($closing->approvedBy)
        <tr>
            <td><strong>Disetujui Oleh:</strong></td>
            <td>{{ $closing->approvedBy->name }}</td>
        </tr>
        @endif
    </table>

    <div class="summary-box">
        <h3 style="margin-top: 0;">Ringkasan Harian</h3>
        <div class="summary-row">
            <strong>Total Penjualan:</strong>
            <strong>Rp {{ number_format($closing->total_sales, 0, ',', '.') }}</strong>
        </div>
        <div class="summary-row">
            <strong>Total Tunai:</strong>
            <strong>Rp {{ number_format($closing->total_cash, 0, ',', '.') }}</strong>
        </div>
        <div class="summary-row">
            <strong>Total Non-Tunai:</strong>
            <strong>Rp {{ number_format($closing->total_non_cash, 0, ',', '.') }}</strong>
        </div>
        <div class="summary-row">
            <strong>Total Void:</strong>
            <strong>Rp {{ number_format($closing->total_void, 0, ',', '.') }}</strong>
        </div>
        <div class="summary-row">
            <strong>Total Refund:</strong>
            <strong>Rp {{ number_format($closing->total_refund, 0, ',', '.') }}</strong>
        </div>
        <hr>
        <div class="summary-row">
            <strong>Total Piutang:</strong>
            <strong>Rp {{ number_format($closing->total_due, 0, ',', '.') }}</strong>
        </div>
    </div>

    <div class="summary-box">
        <h3 style="margin-top: 0;">Penghitungan Kas</h3>
        <div class="summary-row">
            <strong>Kas yang Diharapkan:</strong>
            <strong>Rp {{ number_format($closing->cash_expected, 0, ',', '.') }}</strong>
        </div>
        <div class="summary-row">
            <strong>Kas Fisik:</strong>
            <strong>Rp {{ number_format($closing->cash_actual, 0, ',', '.') }}</strong>
        </div>
        <hr>
        <div class="summary-row">
            <strong>Selisih:</strong>
            <strong style="color: {{ $closing->cash_difference == 0 ? 'green' : 'red' }}">
                Rp {{ number_format($closing->cash_difference, 0, ',', '.') }}
            </strong>
        </div>
    </div>

    <h3>Rincian Per Shift</h3>
    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Kasir</th>
                <th>Sales</th>
                <th>Tunai</th>
                <th>Non-Tunai</th>
                <th>Void</th>
                <th>Refund</th>
                <th>Kas Fisik</th>
                <th>Selisih</th>
            </tr>
        </thead>
        <tbody>
            @forelse($closing->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->cashShift->user->name ?? '-' }}</td>
                    <td>Rp {{ number_format($detail->shift_sales, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($detail->shift_cash, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($detail->shift_non_cash, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($detail->shift_void, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($detail->shift_refund, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($detail->shift_cash_actual, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($detail->shift_cash_difference, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">Tidak ada shift</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-right">Total</th>
                <th>Rp {{ number_format($closing->details->sum('shift_sales'), 0, ',', '.') }}</th>
                <th>Rp {{ number_format($closing->details->sum('shift_cash'), 0, ',', '.') }}</th>
                <th>Rp {{ number_format($closing->details->sum('shift_non_cash'), 0, ',', '.') }}</th>
                <th>Rp {{ number_format($closing->details->sum('shift_void'), 0, ',', '.') }}</th>
                <th>Rp {{ number_format($closing->details->sum('shift_refund'), 0, ',', '.') }}</th>
                <th>Rp {{ number_format($closing->details->sum('shift_cash_actual'), 0, ',', '.') }}</th>
                <th>Rp {{ number_format($closing->details->sum('shift_cash_difference'), 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    @if($closing->notes)
        <div style="margin-top: 15px;">
            <strong>Catatan:</strong>
            <p>{{ $closing->notes }}</p>
        </div>
    @endif

    <div class="signature">
        <div class="signature-box">
            <strong>Kasir Senior</strong>
            <div class="signature-line">____________________</div>
        </div>
        @if($closing->approvedBy)
        <div class="signature-box">
            <strong>Supervisor</strong>
            <div class="signature-line">{{ $closing->approvedBy->name }}</div>
        </div>
        @endif
    </div>
</body>
</html>