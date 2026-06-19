@extends('dashboard.body.main')

@section('title', 'Tambah Transfer Stok')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Tambah Transfer Stok</h5>
                    <a href="{{ route('stock-transfers.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
                <div class="card-body">
                    @if (session()->has('error'))
                        <div class="alert text-white bg-danger" role="alert">
                            <div class="iq-alert-text">{{ session('error') }}</div>
                        </div>
                    @endif

                    <form action="{{ route('stock-transfers.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Dari Lokasi</label>
                                <select name="from_location_id" class="form-control @error('from_location_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Lokasi --</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('from_location_id') == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                                    @endforeach
                                </select>
                                @error('from_location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ke Lokasi</label>
                                <select name="to_location_id" class="form-control @error('to_location_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Lokasi --</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('to_location_id') == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                                    @endforeach
                                </select>
                                @error('to_location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal Transfer</label>
                                <input type="date" name="transfer_date" class="form-control @error('transfer_date') is-invalid @enderror" value="{{ old('transfer_date', date('Y-m-d')) }}" required>
                                @error('transfer_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alasan</label>
                            <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="2" required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Item Transfer</label>
                                <button type="button" class="btn btn-sm btn-primary" id="addRowBtn">+ Tambah Item</button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered inventory-table" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 260px;">Produk</th>
                                            <th style="width: 140px;">Qty</th>
                                            <th>Catatan</th>
                                            <th style="width: 90px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select name="items[0][product_id]" class="form-control" required>
                                                    <option value="">-- Pilih Produk --</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}">{{ $product->name }} (Stok: {{ $product->stock }})</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" name="items[0][quantity]" class="form-control" value="1" min="1" required></td>
                                            <td><input type="text" name="items[0][notes]" class="form-control"></td>
                                            <td><button type="button" class="btn btn-sm btn-danger remove-row">Hapus</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan Transfer</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>

                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Simpan Transfer</button>
                            <a href="{{ route('stock-transfers.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('specificpagescripts')
<script>
let transferRowIndex = 0;

$(document).on('click', '.remove-row', function() {
    if ($('#itemsTable tbody tr').length > 1) {
        $(this).closest('tr').remove();
    }
});

$('#addRowBtn').on('click', function() {
    transferRowIndex++;
    const row = `
        <tr>
            <td>
                <select name="items[${transferRowIndex}][product_id]" class="form-control" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} (Stok: {{ $product->stock }})</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" name="items[${transferRowIndex}][quantity]" class="form-control" value="1" min="1" required></td>
            <td><input type="text" name="items[${transferRowIndex}][notes]" class="form-control"></td>
            <td><button type="button" class="btn btn-sm btn-danger remove-row">Hapus</button></td>
        </tr>`;

    $('#itemsTable tbody').append(row);
});
</script>
@endsection
@endsection
