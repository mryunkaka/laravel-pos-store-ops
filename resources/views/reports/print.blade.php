<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; font-size: 12px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .meta { margin-bottom: 16px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
        tfoot th { background: #eef2ff; }
        .actions { margin-bottom: 16px; }
        .btn { display: inline-block; border: 1px solid #333; color: #111; padding: 6px 12px; text-decoration: none; border-radius: 4px; }
        @media print {
            .actions { display: none; }
        }
    </style>
    <script>
        window.onload = function () { window.print(); }
    </script>
</head>
<body>
    <div class="actions">
        <button class="btn" onclick="window.print()">Cetak / Simpan PDF</button>
        <button class="btn" onclick="window.close()">Tutup</button>
    </div>

    <h1>{{ $title }}</h1>
    <div class="meta">Periode {{ $startDate }} sampai {{ $endDate }}</div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                @foreach($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    @foreach($columns as $column)
                        @php($value = $row[$column] ?? '')
                        <td>
                            @if(is_numeric($value) && !in_array($column, ['Transaksi', 'Qty', 'Qty Terjual', 'Stok', 'Stok Minimum'], true))
                                Rp {{ number_format($value, 0, ',', '.') }}
                            @else
                                {{ $value }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) + 1 }}">Data tidak ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
