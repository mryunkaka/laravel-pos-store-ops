@extends('dashboard.body.main')

@section('title', 'Detail Retur Penjualan')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Retur Penjualan {{ $salesReturn->return_number }}</h5>
                    <div class="inventory-actions mt-2 mt-sm-0">
                        <a href="{{ route('sales-returns.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert text-white bg-success" role="alert">{{ session('success') }}</div>
                    @endif
                    @if (session()->has('error'))
                        <div class="alert text-white bg-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-3"><div class="alert alert-light border mb-0">Invoice<br><strong>{{ $salesReturn->order->invoice_no ?? '-' }}</strong></div></div>
                        <div class="col-md-3"><div class="alert alert-light border mb-0">Tanggal<br><strong>{{ $salesReturn->return_date->format('Y-m-d') }}</strong></div></div>
                        <div class="col-md-3"><div class="alert alert-light border mb-0">Status<br><strong>{{ ucfirst($salesReturn->status) }}</strong></div></div>
                        <div class="col-md-3"><div class="alert alert-light border mb-0">Refund<br><strong>Rp {{ number_format($salesReturn->refund_amount, 0, ',', '.') }}</strong></div></div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Kondisi</th>
                                    <th>Harga</th>
                                    <th>Total</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($salesReturn->details as $detail)
                                    <tr>
                                        <td>{{ $detail->product->name ?? '-' }}</td>
                                        <td>{{ $detail->quantity }}</td>
                                        <td>{{ $detail->condition === 'sellable' ? 'Layak Jual' : 'Rusak' }}</td>
                                        <td>Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                                        <td>{{ $detail->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($salesReturn->isPending())
                        <div class="inventory-actions mt-3">
                            <form action="{{ route('sales-returns.complete', $salesReturn->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success">Selesaikan Retur</button>
                            </form>
                            <form action="{{ route('sales-returns.cancel', $salesReturn->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-danger">Batalkan</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
