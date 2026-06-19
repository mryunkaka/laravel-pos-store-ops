@extends('dashboard.body.main')

@section('title', 'Detail Shift Kasir')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Shift Kasir #{{ $shift->id }}</h5>
                    <div class="inventory-actions mt-2 mt-sm-0">
                        <a href="{{ route('cash-shifts.print', $shift->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm">Cetak</a>
                        <a href="{{ route('cash-shifts.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert text-white bg-success" role="alert">{{ session('success') }}</div>
                    @endif
                    @if (session()->has('error'))
                        <div class="alert text-white bg-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    <!-- Summary -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Kas Awal</h6>
                                    <h3 class="mb-0">Rp {{ number_format($shift->opening_balance, 0) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Sales</h6>
                                    <h3 class="mb-0">Rp {{ number_format($shift->total_sales, 0) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Kas Akhir</h6>
                                    <h3 class="mb-0">Rp {{ number_format($shift->closing_balance, 0) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Selisih</h6>
                                    <h3 class="mb-0">Rp {{ number_format($shift->closing_balance - ($shift->opening_balance + $shift->total_cash), 0, ',', '.') }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($shift->status === 'active')
                        <div class="row mb-4">
                            <div class="col-lg-6">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Catat Kas Masuk / Keluar</h6>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('cash-shifts.movements.store', $shift->id) }}" method="POST">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-4 form-group">
                                                    <label>Tipe</label>
                                                    <select name="transaction_type" class="form-control" required>
                                                        <option value="cash_in">Kas Masuk</option>
                                                        <option value="cash_out">Kas Keluar</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 form-group">
                                                    <label>Jumlah</label>
                                                    <input type="number" name="amount" class="form-control" min="1" step="100" required>
                                                </div>
                                                <div class="col-md-4 form-group">
                                                    <label>Keterangan</label>
                                                    <input type="text" name="description" class="form-control" maxlength="500" required>
                                                </div>
                                            </div>
                                            <div class="inventory-actions">
                                                <button type="submit" class="btn btn-primary">Simpan Catatan Kas</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Tutup Shift</h6>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('cash-shifts.close', $shift->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="form-group">
                                                <label>Uang Fisik Kasir</label>
                                                <input type="number" name="closing_balance" class="form-control" min="0" step="100" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Catatan Tutup Shift</label>
                                                <textarea name="closing_notes" class="form-control" rows="2"></textarea>
                                            </div>
                                            <div class="inventory-actions">
                                                <button type="submit" class="btn btn-danger">Tutup Shift</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Transaction Table -->
                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Tipe</th>
                                    <th>Detail</th>
                                    <th>Pembayaran</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shift->details as $detail)
                                    <tr>
                                        <td>{{ $detail->transaction_time->format('H:i') }}</td>
                                        <td>{{ $detail->transaction_type }}</td>
                                        <td>{{ $detail->description }}</td>
                                        <td>{{ $detail->payment_type }}</td>
                                        <td>Rp {{ number_format($detail->amount, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center">No data</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light">
                                <tr><th colspan="4" class="text-right">Total</th><th>Rp {{ $shift->details->sum('amount') }}</th></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
