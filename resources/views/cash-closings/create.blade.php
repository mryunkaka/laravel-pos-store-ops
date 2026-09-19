@extends('dashboard.body.main')

@section('title', 'Buat Tutup Kasir Harian')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Buat Tutup Kasir Harian</h5>
                    <a href="{{ route('cash-closings.index') }}" class="btn btn-secondary btn-sm mt-2 mt-sm-0">Kembali</a>
                </div>
                <div class="card-body">
                    @if (session()->has('error'))
                        <div class="alert text-white bg-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    <form method="GET" action="{{ route('cash-closings.create') }}" class="inventory-filter">
                        <div class="form-group">
                            <label>Tanggal Shift</label>
                            <input type="date" name="date" class="form-control" value="{{ $date }}">
                        </div>
                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Tampilkan</button>
                        </div>
                    </form>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6>Total Sales</h6>
                                    <h4 class="mb-0">{{ format_rupiah($totalSales) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6>Tunai</h6>
                                    <h4 class="mb-0">{{ format_rupiah($totalCash) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6>Non Tunai</h6>
                                    <h4 class="mb-0">{{ format_rupiah($totalNonCash) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6>Void / Refund</h6>
                                    <h4 class="mb-0">{{ format_rupiah($totalVoid + $totalRefund) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mt-2">
                            <div class="alert alert-light border mb-0">
                                Total piutang dari shift terpilih: <strong>{{ format_rupiah($totalDue) }}</strong>
                            </div>
                        </div>
                    </div>

                    @if ($shifts->isEmpty())
                        <div class="alert alert-info mb-0">
                            Tidak ada shift tertutup yang belum masuk tutup kasir untuk tanggal ini.
                        </div>
                    @else
                        <form action="{{ route('cash-closings.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="closing_date" value="{{ $date }}">

                            <div class="table-responsive">
                                <table class="table table-hover inventory-table">
                                    <thead>
                                        <tr>
                                            <th>Kasir</th>
                                            <th>Jam</th>
                                            <th>Total Sales</th>
                                            <th>Tunai</th>
                                            <th>Non Tunai</th>
                                            <th>Kas Fisik Harian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($shifts as $shift)
                                            <tr>
                                                <td>
                                                    {{ $shift->user->name ?? '-' }}
                                                    <input type="hidden" name="shifts[{{ $loop->index }}][id]" value="{{ $shift->id }}">
                                                </td>
                                                <td>{{ $shift->start_time->format('H:i') }} - {{ optional($shift->end_time)->format('H:i') }}</td>
                                                <td>{{ format_rupiah($shift->total_sales) }}</td>
                                                <td>{{ format_rupiah($shift->total_cash) }}</td>
                                                <td>{{ format_rupiah($shift->total_non_cash) }}</td>
                                                <td>
                                                    <input type="number" name="shifts[{{ $loop->index }}][cash_actual]" class="form-control" value="{{ old('shifts.' . $loop->index . '.cash_actual', $shift->closing_balance) }}" min="0" step="100" required>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="form-group">
                                <label>Catatan</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                            </div>

                            <div class="inventory-actions">
                                <button type="submit" class="btn btn-primary">Simpan Tutup Kasir</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
