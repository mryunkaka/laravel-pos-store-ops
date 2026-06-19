@extends('dashboard.body.main')

@section('title', 'Detail Shift Kasir')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Shift Kasir #{{ $shift->id }}</h5>
                </div>
                <div class="card-body">
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
                                    <h3 class="mb-0">Rp {{ number_format($shift->closing_balance - $shift->opening_balance, 0) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
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