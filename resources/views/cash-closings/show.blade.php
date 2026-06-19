@extends('dashboard.body.main')

@section('title', 'Detail Tutup Kasir')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Tutup Kasir {{ $closing->closing_date }}</h5>
                    <div class="inventory-actions mt-2 mt-sm-0">
                        <a href="{{ route('cash-closings.print', $closing->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm">Cetak</a>
                        <a href="{{ route('cash-closings.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Summary -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Sales</h6>
                                    <h3 class="mb-0">Rp {{ number_format($closing->total_sales, 0) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Tunai</h6>
                                    <h3 class="mb-0">Rp {{ number_format($closing->total_cash, 0) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Non-Tunai</h6>
                                    <h3 class="mb-0">Rp {{ number_format($closing->total_non_cash, 0) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Selisih Kas</h6>
                                    <h3 class="mb-0">Rp {{ number_format($closing->cash_difference, 0) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shift Details -->
                    <h6 class="mb-3">Detail Per Shift</h6>
                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
                            <thead>
                                <tr>
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
                                @forelse($closing->details as $detail)
                                    <tr>
                                        <td>{{ $detail->cashShift->user->name ?? '-' }}</td>
                                        <td>Rp {{ number_format($detail->shift_sales, 0) }}</td>
                                        <td>Rp {{ number_format($detail->shift_cash, 0) }}</td>
                                        <td>Rp {{ number_format($detail->shift_non_cash, 0) }}</td>
                                        <td>Rp {{ number_format($detail->shift_void, 0) }}</td>
                                        <td>Rp {{ number_format($detail->shift_refund, 0) }}</td>
                                        <td>Rp {{ number_format($detail->shift_cash_actual, 0) }}</td>
                                        <td>Rp {{ number_format($detail->shift_cash_difference, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center">No data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
