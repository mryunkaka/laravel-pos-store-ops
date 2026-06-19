@extends('dashboard.body.main')

@section('title', 'Tambah Penerimaan Barang')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tambah Penerimaan Barang</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('purchase-receivings.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Purchase Order</label>
                            <select name="purchase_order_id" class="form-select @error('purchase_order_id') is-invalid @enderror" required>
                                <option value="">-- Pilih PO --</option>
                                @foreach($purchaseOrders as $po)
                                    <option value="{{ $po->id }}" data-total="{{ $po->pending_quantity }}">
                                        {{ $po->po_number }} - {{ $po->supplier->name }} (Pending: {{ $po->pending_quantity }})
                                    </option>
                                @endforeach
                            </select>
                            @error('purchase_order_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Receiving</label>
                            <input type="date" name="receiving_date" class="form-control @error('receiving_date') is-invalid @enderror" value="{{ old('receiving_date', date('Y-m-d')) }}" required>
                            @error('receiving_date')
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
                                            <th>Qty PO</th>
                                            <th>Qty Receiving</th>
                                            <th>Max</th>
                                            <th>Harga</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="itemRow0">
                                            <td>
                                                <select name="items[0][product_id]" class="form-select product-select" required disabled>
                                                    <option value="">-- Pilih Produk --</option>
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control qty-po" readonly></td>
                                            <td><input type="number" name="items[0][received_quantity]" class="form-control qty-input" value="0" min="0" required></td>
                                            <td><input type="number" class="form-control qty-max" readonly></td>
                                            <td><input type="text" class="form-control price-display" readonly></td>
                                            <td><input type="text" class="form-control total-display" readonly></td>
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

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Simpan Receiving</button>
                            <a href="{{ route('purchase-receivings.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedPO = null;
let products = [];

@foreach($products as $p)
    products[{{ $p->id }}] = { name: "{{ $p->name }}", buying_price: {{ $p->buying_price }} };
@endforeach

$(document).on('change', 'select[name="purchase_order_id"]', function() {
    const poId = $(this).val();
    selectedPO = poId;
    
    // Reset items
    $('#itemsTable tbody').html('');
    
    // Populate items from PO details
    $.ajax({
        url: '/api/purchase-order/' + poId + '/details',
        method: 'GET',
        success: function(response) {
            response.forEach((detail, index) => {
                const row = `
                    <tr id="itemRow${index}">
                        <td>${detail.product.name}</td>
                        <td><input type="number" class="form-control qty-po" value="${detail.quantity}" readonly></td>
                        <td><input type="number" name="items[${index}][received_quantity]" class="form-control qty-input" value="0" min="0" max="${detail.quantity}" required></td>
                        <td><input type="number" class="form-control qty-max" value="${detail.quantity}" readonly></td>
                        <td><input type="text" class="form-control price-display" value="${detail.unit_price}" readonly></td>
                        <td><input type="text" class="form-control total-display" readonly></td>
                    </tr>`;
                $('#itemsTable tbody').append(row);
            });
            calculateTotals();
        }
    });
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
@endpush
@endsection
