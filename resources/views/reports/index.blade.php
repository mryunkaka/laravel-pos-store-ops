@extends('dashboard.body.main')

@section('title', 'Laporan')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">{{ $title }}</h5>
                    <div class="d-flex flex-wrap" style="gap: 8px;">
                        <a href="{{ route('reports.export.excel', request()->query()) }}" class="btn btn-success">Export Excel</a>
                        <a href="{{ route('reports.export.pdf', request()->query()) }}" target="_blank" class="btn btn-danger">Export PDF</a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="inventory-filter">
                        <div class="form-group">
                            <label>Jenis Laporan</label>
                            <select name="type" class="form-control">
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}" {{ $type === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date', $startDate) }}">
                        </div>
                        <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date', $endDate) }}">
                        </div>
                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
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
                                        <td colspan="{{ count($columns) + 1 }}" class="text-center">Data tidak ditemukan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(!empty($rows))
                                <tfoot>
                                    <tr>
                                        <th colspan="1">Total</th>
                                        @foreach($columns as $column)
                                            <th>
                                                @if(isset($totals[$column]))
                                                    @if(in_array($column, ['Transaksi', 'Qty', 'Qty Terjual', 'Stok', 'Stok Minimum'], true))
                                                        {{ number_format($totals[$column], 0, ',', '.') }}
                                                    @else
                                                        Rp {{ number_format($totals[$column], 0, ',', '.') }}
                                                    @endif
                                                @endif
                                            </th>
                                        @endforeach
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>

                    @if($type === 'gross-profit')
                        <p class="text-muted mt-3 mb-0">Laba memakai snapshot harga beli di detail transaksi. Untuk transaksi lama yang belum punya snapshot, laporan memakai harga beli produk saat ini sebagai fallback.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
