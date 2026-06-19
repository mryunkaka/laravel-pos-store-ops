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
                            <button type="submit" class="btn btn-primary">Simpan PO</button>
                            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let rowIndex = 0;

function updateTotals() {
    let subTotal = 0;
    $('.total-display').each(function() {
        subTotal += parseFloat($(this).val()) || 0;
    });
    $('#subTotal').val(subTotal.toLocaleString('id-ID'));
    $('#vat').val('0');
    $('#total').val(subTotal.toLocaleString('id-ID'));
}

function updateProductPrice() {
    const productSelect = $(this);
    const row = productSelect.closest('tr');
    const priceInput = row.find('.price-input');
    const selectedOption = productSelect.find(':selected');
    const price = selectedOption.data('price') || 0;
    priceInput.val(price);
    updateTotals();
}

function updateItemTotal() {
    const row = $(this).closest('tr');
    const qty = parseFloat(row.find('.qty-input').val()) || 0;
    const price = parseFloat(row.find('.price-input').val()) || 0;
    const total = qty * price;
    row.find('.total-display').val(total.toLocaleString('id-ID'));
    updateTotals();
}

function addRow() {
    rowIndex++;
    const newRow = `
        <tr id="itemRow${rowIndex}">
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-select product-select" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->buying_price }}">
                            {{ $product->name }} (Stok: {{ $product->stock }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control qty-input" value="1" min="1" required></td>
            <td><input type="number" name="items[${rowIndex}][unit_price]" class="form-control price-input" readonly></td>
            <td><input type="number" name="items[${rowIndex}][total]" class="form-control total-display" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
        </tr>`;
    $('#itemsTable tbody').append(newRow);
    
    $('#itemsTable tbody tr:last .product-select').on('change', updateProductPrice);
    $('#itemsTable tbody tr:last .qty-input').on('input', updateItemTotal);
    $('#itemsTable tbody tr:last .price-input').on('input', updateItemTotal);
}

$(document).on('click', '.remove-row', function() {
    $(this).closest('tr').remove();
    updateTotals();
});

$(document).on('change', '.product-select', updateProductPrice);
$(document).on('input', '.qty-input, .price-input', updateItemTotal);
$('#addRowBtn').on('click', addRow);

updateTotals();
</script>
@endpush
@endsection