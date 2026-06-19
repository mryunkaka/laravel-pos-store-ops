@extends('dashboard.body.main')

@section('title', 'Tambah Penerimaan Barang')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tambah Penerimaan Barang</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('purchase-receivings.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Order Pembelian</label>
                            <select name="purchase_order_id" class="form-control @error('purchase_order_id') is-invalid @enderror" required>
                                <option value="">-- Pilih PO --</option>
                                @foreach($purchaseOrders as $po)
                                    <option value="{{ $po->id }}">
                                        {{ $po->po_number }} - {{ $po->supplier->name }} (Sisa: {{ $po->pending_quantity }})
                                    </option>
                                @endforeach
                            </select>
                            @error('purchase_order_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Penerimaan</label>
                            <input type="date" name="receiving_date" class="form-control @error('receiving_date') is-invalid @enderror" value="{{ old('receiving_date', date('Y-m-d')) }}" required>
                            @error('receiving_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Item</label>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Qty PO</th>
                                            <th>Sisa</th>
                                            <th>Qty Diterima</th>
                                            <th>Qty Ditolak</th>
                                            <th>Harga</th>
                                            <th>Total</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchaseOrders as $po)
                                            @foreach($po->details as $detail)
                                                @if($detail->pending_quantity > 0)
                                                    <tr class="po-item d-none" data-po-id="{{ $po->id }}">
                                                        <td>
                                                            {{ $detail->product->name }}
                                                            <input type="hidden" name="items[{{ $detail->id }}][purchase_order_detail_id]" value="{{ $detail->id }}" disabled>
                                                            <input type="hidden" name="items[{{ $detail->id }}][product_id]" value="{{ $detail->product_id }}" disabled>
                                                        </td>
                                                        <td>{{ $detail->quantity }}</td>
                                                        <td>{{ $detail->pending_quantity }}</td>
                                                        <td>
                                                            <input type="number" name="items[{{ $detail->id }}][received_quantity]" class="form-control qty-input" value="0" min="0" max="{{ $detail->pending_quantity }}" disabled>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="items[{{ $detail->id }}][rejected_quantity]" class="form-control rejected-input" value="0" min="0" max="{{ $detail->pending_quantity }}" disabled>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control price-display" value="{{ $detail->unit_price }}" readonly>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control total-display" readonly value="0">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="items[{{ $detail->id }}][notes]" class="form-control" disabled>
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
                            <button type="submit" class="btn btn-primary">Simpan Penerimaan</button>
                            <a href="{{ route('purchase-receivings.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('specificpagescripts')
<script>
$(document).on('change', 'select[name="purchase_order_id"]', function() {
    const poId = $(this).val();

    $('.po-item').addClass('d-none').find(':input').prop('disabled', true);
    $('.po-item[data-po-id="' + poId + '"]').removeClass('d-none').find(':input').prop('disabled', false);
    calculateTotals();
});

function calculateTotals() {
    let subTotal = 0;
    $('.total-display').each(function() {
        subTotal += parseFloat($(this).val()) || 0;
    });
    $('#subTotal').val(subTotal.toLocaleString('id-ID'));
    $('#vat').val('0');
    $('#total').val(subTotal.toLocaleString('id-ID'));
}

$(document).on('input', '.qty-input', function() {
    const qty = parseFloat($(this).val()) || 0;
    const price = parseFloat($(this).closest('tr').find('.price-display').val()) || 0;
    $(this).closest('tr').find('.total-display').val((qty * price).toLocaleString('id-ID'));
    calculateTotals();
});
</script>
@endsection
@endsection
