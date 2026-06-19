@extends('dashboard.body.main')

@section('title', 'Detail Retur Pembelian')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Detail Retur {{ $return->return_number }}</h5>
                    <a href="{{ route('purchase-returns.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Penerimaan:</strong> {{ $return->purchaseReceiving->receiving_number ?? '-' }}</div>
                        <div class="col-md-4"><strong>Pemasok:</strong> {{ $return->supplier->name ?? '-' }}</div>
                        <div class="col-md-4"><strong>Status:</strong> {{ ucfirst($return->status) }}</div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty Retur</th>
                                    <th>Harga</th>
                                    <th>Total</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($return->details as $detail)
                                    <tr>
                                        <td>{{ $detail->product->name ?? '-' }}</td>
                                        <td>{{ $detail->quantity }}</td>
                                        <td>Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                                        <td>{{ $detail->description ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($return->status === 'pending')
                        <form action="{{ route('purchase-returns.complete', $return->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success" onclick="return confirm('Selesaikan retur ini?')">Selesaikan Retur</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
