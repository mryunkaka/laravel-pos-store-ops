@extends('dashboard.body.main')

@section('title', 'Tambah Retur Pembelian')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tambah Retur Pembelian</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('purchase-returns.store') }}" method="POST" id="returnForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Penerimaan Barang</label>
                            <select name="purchase_receiving_id" class="form-select @error('purchase_receiving_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Receiving --</option>
                                @foreach($receivings as $receiving)
                                    <option value="{{ $receiving->id }}" data-supplier="{{ $receiving->supplier_id }}">
                                        {{ $receiving->receiving_number }} - {{ $receiving->supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('purchase_receiving_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Retur</label>
                            <input type="date" name="return_date" class="form-control @error('return_date') is-invalid @enderror" value="{{ old('return_date', date('Y-m-d')) }}" required>
                            @error('return_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Items</label>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Qty Received</th>
                                            <th>Qty Return</th>
                                            <th>Harga</th>
                                            <th>Total</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="itemRow0">
                                            <td>
                                                <select name="items[0][product_id]" class="form-select product-select" required disabled>
                                                    <option value="">-- Pilih Produk --</option>
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control qty-received" readonly></td>
                                            <td><input type="number" name="items[0][quantity]" class="form-control qty-return" value="0" min="1" required></td>
                                            <td><input type="text" class="form-control price-display" readonly></td>
                                            <td><input type="text" class="form-control total-display" readonly></td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Sub Total</label>
                                    <input type="text" id="subTotal" class="form-control" readonly value="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Diskon</label>
                                    <input type="number" name="discount" id="discount" class="form-control" value="0" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Total</label>
                                    <input type="text" id="total" class="form-control" readonly value="0">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Simpan Retur</button>
                            <a href="{{ route('purchase-returns.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>