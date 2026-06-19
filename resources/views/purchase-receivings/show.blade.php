@extends('dashboard.body.main')

@section('title', 'Detail Penerimaan Barang')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Detail Penerimaan {{ $receiving->receiving_number }}</h5>
                    <a href="{{ route('purchase-receivings.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>PO:</strong> {{ $receiving->purchaseOrder->po_number ?? '-' }}</div>
                        <div class="col-md-4"><strong>Pemasok:</strong> {{ $receiving->supplier->name ?? '-' }}</div>
                        <div class="col-md-4"><strong>Status:</strong> {{ ucfirst($receiving->status) }}</div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty Diterima</th>
                                    <th>Qty Ditolak</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($receiving->details as $detail)
                                    <tr>
                                        <td>{{ $detail->product->name ?? '-' }}</td>
                                        <td>{{ $detail->received_quantity }}</td>
                                        <td>{{ $detail->rejected_quantity }}</td>
                                        <td>{{ $detail->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($receiving->status === 'pending')
                        <form action="{{ route('purchase-receivings.complete', $receiving->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success" onclick="return confirm('Selesaikan penerimaan ini?')">Selesaikan Penerimaan</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
