@extends('dashboard.body.main')

@section('title', 'Tambah Retur Pembelian')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tambah Retur Pembelian</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('purchase-returns.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Penerimaan Barang</label>
                            <select name="purchase_receiving_id" class="form-control @error('purchase_receiving_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Penerimaan --</option>
                                @foreach($receivings as $receiving)
                                    <option value="{{ $receiving->id }}">
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
                            <label class="form-label">Item Retur</label>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Qty Diterima</th>
                                            <th>Qty Retur</th>
                                            <th>Harga</th>
                                            <th>Total</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($receivings as $receiving)
                                            @foreach($receiving->details as $detail)
                                                @if($detail->received_quantity > 0)
                                                    <tr class="return-item d-none" data-receiving-id="{{ $receiving->id }}">
                                                        <td>
                                                            {{ $detail->product->name }}
                                                            <input type="hidden" name="items[{{ $detail->id }}][product_id]" value="{{ $detail->product_id }}" disabled>
                                                        </td>
                                                        <td>{{ $detail->received_quantity }}</td>
                                                        <td>
                                                            <input type="number" name="items[{{ $detail->id }}][quantity]" class="form-control qty-return" value="0" min="0" max="{{ $detail->received_quantity }}" disabled>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="items[{{ $detail->id }}][unit_price]" class="form-control price-input" value="{{ $detail->product->buying_price }}" min="0" readonly disabled>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="items[{{ $detail->id }}][total]" class="form-control total-input" value="0" min="0" readonly disabled>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="items[{{ $detail->id }}][description]" class="form-control" disabled>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endforeach
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

                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Simpan Retur</button>
                            <a href="{{ route('purchase-returns.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('specificpagescripts')
<script>
$(document).on('change', 'select[name="purchase_receiving_id"]', function() {
    const receivingId = $(this).val();

    $('.return-item').addClass('d-none').find(':input').prop('disabled', true);
    $('.return-item[data-receiving-id="' + receivingId + '"]').removeClass('d-none').find(':input').prop('disabled', false);
    calculateReturnTotals();
});

$(document).on('input', '.qty-return, #discount', function() {
    const row = $(this).closest('tr');
    const qty = parseFloat(row.find('.qty-return').val()) || 0;
    const price = parseFloat(row.find('.price-input').val()) || 0;

    row.find('.total-input').val(qty * price);
    calculateReturnTotals();
});

function calculateReturnTotals() {
    let subTotal = 0;

    $('.return-item:not(.d-none) .total-input').each(function() {
        subTotal += parseFloat($(this).val()) || 0;
    });

    const discount = parseFloat($('#discount').val()) || 0;
    $('#subTotal').val(subTotal.toLocaleString('id-ID'));
    $('#total').val(Math.max(subTotal - discount, 0).toLocaleString('id-ID'));
}
</script>
@endsection
@endsection
