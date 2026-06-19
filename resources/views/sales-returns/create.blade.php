@extends('dashboard.body.main')

@section('title', 'Buat Retur Penjualan')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Buat Retur Penjualan</h5>
                    <a href="{{ route('sales-returns.index') }}" class="btn btn-secondary btn-sm mt-2 mt-sm-0">Kembali</a>
                </div>
                <div class="card-body">
                    @if (session()->has('error'))
                        <div class="alert text-white bg-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    <form method="GET" action="{{ route('sales-returns.create') }}" class="inventory-filter">
                        <div class="form-group">
                            <label>Order Asal</label>
                            <select name="order_id" class="form-control">
                                <option value="">Pilih invoice complete</option>
                                @foreach ($orders as $order)
                                    <option value="{{ $order->id }}" {{ request('order_id') == $order->id ? 'selected' : '' }}>
                                        {{ $order->invoice_no }} - {{ $order->customer->name ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Tampilkan</button>
                        </div>
                    </form>

                    @if ($selectedOrder)
                        <form action="{{ route('sales-returns.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $selectedOrder->id }}">

                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <label>Tanggal Retur</label>
                                    <input type="date" name="return_date" class="form-control" value="{{ old('return_date', now()->toDateString()) }}" required>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Tipe Retur</label>
                                    <select name="return_type" class="form-control" required>
                                        <option value="refund">Refund</option>
                                        <option value="exchange">Tukar Barang</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Alasan</label>
                                    <input type="text" name="reason" class="form-control" value="{{ old('reason') }}" required>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover inventory-table">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Terjual</th>
                                            <th>Qty Retur</th>
                                            <th>Kondisi</th>
                                            <th>Harga</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($selectedOrder->details as $detail)
                                            @php
                                                $returned = $detail->salesReturnDetails
                                                    ->filter(fn ($item) => $item->salesReturn && $item->salesReturn->status === 'completed')
                                                    ->sum('quantity');
                                                $available = $detail->quantity - $returned;
                                            @endphp
                                            <tr>
                                                <td>{{ $detail->product->name ?? '-' }}</td>
                                                <td>{{ $detail->quantity }} (sisa {{ $available }})</td>
                                                <td>
                                                    <input type="number" name="items[{{ $detail->id }}][quantity]" class="form-control" min="0" max="{{ $available }}" value="{{ old('items.' . $detail->id . '.quantity', 0) }}" {{ $available <= 0 ? 'disabled' : '' }}>
                                                </td>
                                                <td>
                                                    <select name="items[{{ $detail->id }}][condition]" class="form-control" {{ $available <= 0 ? 'disabled' : '' }}>
                                                        <option value="sellable">Layak Jual</option>
                                                        <option value="damaged">Rusak</option>
                                                    </select>
                                                </td>
                                                <td>Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                                <td>
                                                    <input type="text" name="items[{{ $detail->id }}][notes]" class="form-control" {{ $available <= 0 ? 'disabled' : '' }}>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="inventory-actions">
                                <button type="submit" class="btn btn-primary">Simpan Retur</button>
                                <a href="{{ route('sales-returns.index') }}" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
