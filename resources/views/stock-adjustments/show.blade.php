@extends('dashboard.body.main')

@section('title', 'Detail Penyesuaian Stok')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Detail Penyesuaian {{ $adjustment->adjustment_number }}</h5>
                    <a href="{{ route('stock-adjustments.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr><th>Produk</th><td>{{ $adjustment->product->name ?? '-' }}</td></tr>
                        <tr><th>Tanggal</th><td>{{ $adjustment->adjustment_date }}</td></tr>
                        <tr><th>Tipe</th><td>{{ $adjustment->type === 'in' ? 'Tambah' : 'Kurang' }}</td></tr>
                        <tr><th>Qty</th><td>{{ $adjustment->quantity }}</td></tr>
                        <tr><th>Stok Lama</th><td>{{ $adjustment->old_stock }}</td></tr>
                        <tr><th>Stok Baru</th><td>{{ $adjustment->new_stock }}</td></tr>
                        <tr><th>Alasan</th><td>{{ $adjustment->reason }}</td></tr>
                        <tr><th>Dibuat oleh</th><td>{{ $adjustment->user->name ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
