@extends('dashboard.body.main')

@section('title', 'Detail Order Pembelian')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Detail Order Pembelian {{ $purchaseOrder->po_number }}</h5>
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Pemasok:</strong> {{ $purchaseOrder->supplier->name ?? '-' }}</div>
                        <div class="col-md-4"><strong>Tanggal PO:</strong> {{ $purchaseOrder->po_date }}</div>
                        <div class="col-md-4"><strong>Status:</strong> {{ ucfirst($purchaseOrder->status) }}</div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Harga</th>
                                    <th>Total</th>
                                    <th>Sisa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->details as $detail)
                                    <tr>
                                        <td>{{ $detail->product->name ?? '-' }}</td>
                                        <td>{{ $detail->quantity }}</td>
                                        <td>Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                                        <td>{{ $detail->pending_quantity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end">
                        <strong>Total: Rp {{ number_format($purchaseOrder->total, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
