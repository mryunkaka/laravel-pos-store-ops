@extends('dashboard.body.main')

@section('title', 'Detail Transfer Stok')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Detail Transfer {{ $stockTransfer->transfer_number }}</h5>
                    <a href="{{ route('stock-transfers.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Dari:</strong> {{ $stockTransfer->fromLocation->name ?? '-' }}</div>
                        <div class="col-md-3"><strong>Ke:</strong> {{ $stockTransfer->toLocation->name ?? '-' }}</div>
                        <div class="col-md-3"><strong>Tanggal:</strong> {{ $stockTransfer->transfer_date }}</div>
                        <div class="col-md-3"><strong>Status:</strong> {{ ucfirst($stockTransfer->status) }}</div>
                    </div>
                    <div class="mb-3"><strong>Alasan:</strong> {{ $stockTransfer->reason }}</div>

                    <div class="table-responsive">
                        <table class="table table-bordered inventory-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockTransfer->details as $detail)
                                    <tr>
                                        <td>{{ $detail->product->name ?? '-' }}</td>
                                        <td>{{ $detail->quantity }}</td>
                                        <td>{{ $detail->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($stockTransfer->status === 'pending')
                        <form action="{{ route('stock-transfers.complete', $stockTransfer->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success" onclick="return confirm('Selesaikan transfer stok ini?')">Selesaikan Transfer</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
