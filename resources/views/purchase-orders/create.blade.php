@extends('dashboard.body.main')

@section('title', 'Tambah Order Pembelian')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Tambah Order Pembelian</h5>
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('purchase-orders.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Pemasok</label>
                                <select name="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Pemasok --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal PO</label>
                                <input type="date" name="po_date" class="form-control @error('po_date') is-invalid @enderror" value="{{ old('po_date', date('Y-m-d')) }}" required>
                                @error('po_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Estimasi Datang</label>
                                <input type="date" name="expected_delivery_date" class="form-control @error('expected_delivery_date') is-invalid @enderror" value="{{ old('expected_delivery_date') }}">
                                @error('expected_delivery_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Item Order Pembelian</label>
                                <button type="button" class="btn btn-sm btn-primary" id="addRowBtn">+ Tambah Item</button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 260px;">Produk</th>
                                            <th style="width: 130px;">Qty</th>
                                            <th style="width: 160px;">Harga</th>
                                            <th style="width: 160px;">Total</th>
                                            <th style="width: 80px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select name="items[0][product_id]" class="form-control product-select" required>
                                                    <option value="">-- Pilih Produk --</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" data-price="{{ $product->buying_price }}">
                                                            {{ $product->name }} (Stok: {{ $product->stock }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][quantity]" class="form-control qty-input" value="1" min="1" required>
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][unit_price]" class="form-control price-input" min="0" step="0.01" required>
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][total]" class="form-control total-display" min="0" step="0.01" readonly>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button>
                                            </td>
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
                                    <label class="form-label">PPN (0%)</label>
                                    <input type="text" id="vat" class="form-control" readonly value="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Total</label>
                                    <input type="text" id="total" class="form-control" readonly value="0">
                                </div>
                            </div>
                        </div>

                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Simpan PO</button>
                            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('specificpagescripts')
<script>
let rowIndex = 0;

function formatNumber(value) {
    return Number(value || 0).toLocaleString('id-ID');
}

function updateTotals() {
    let subTotal = 0;

    $('.total-display').each(function() {
        subTotal += parseFloat($(this).val()) || 0;
    });

    $('#subTotal').val(formatNumber(subTotal));
    $('#vat').val('0');
    $('#total').val(formatNumber(subTotal));
}

function updateItemTotal(row) {
    const qty = parseFloat(row.find('.qty-input').val()) || 0;
    const price = parseFloat(row.find('.price-input').val()) || 0;
    row.find('.total-display').val(qty * price);
    updateTotals();
}

$(document).on('change', '.product-select', function() {
    const row = $(this).closest('tr');
    const price = $(this).find(':selected').data('price') || 0;
    row.find('.price-input').val(price);
    updateItemTotal(row);
});

$(document).on('input', '.qty-input, .price-input', function() {
    updateItemTotal($(this).closest('tr'));
});

$(document).on('click', '.remove-row', function() {
    if ($('#itemsTable tbody tr').length > 1) {
        $(this).closest('tr').remove();
        updateTotals();
    }
});

$('#addRowBtn').on('click', function() {
    rowIndex++;
    const row = `
        <tr>
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-control product-select" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->buying_price }}">
                            {{ $product->name }} (Stok: {{ $product->stock }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input" value="1" min="1" required></td>
            <td><input type="number" name="items[${rowIndex}][unit_price]" class="form-control price-input" min="0" step="0.01" required></td>
            <td><input type="number" name="items[${rowIndex}][total]" class="form-control total-display" min="0" step="0.01" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button></td>
        </tr>`;

    $('#itemsTable tbody').append(row);
});

updateTotals();
</script>
@endsection
@endsection
