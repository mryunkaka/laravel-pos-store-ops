@extends('dashboard.body.main')

@section('title', 'Tambah Penyesuaian Stok')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tambah Penyesuaian Stok</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('stock-adjustments.store') }}" method="POST" id="adjustmentForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Produk</label>
                            <select name="product_id" class="form-control @error('product_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-stock="{{ $product->stock }}">
                                        {{ $product->name }} (Stok: {{ $product->stock }})
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Penyesuaian</label>
                            <input type="date" name="adjustment_date" class="form-control @error('adjustment_date') is-invalid @enderror" value="{{ old('adjustment_date', date('Y-m-d')) }}" required>
                            @error('adjustment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipe Penyesuaian</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="adjustment_type" id="typeIncrease" value="increase" checked>
                                <label class="form-check-label" for="typeIncrease">Tambah Stok</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="adjustment_type" id="typeDecrease" value="decrease">
                                <label class="form-check-label" for="typeDecrease">Kurang Stok</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jumlah</label>
                            <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" min="1" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alasan</label>
                            <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="3" required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Stok Saat Ini</label>
                                    <input type="text" id="currentStock" class="form-control" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Stok Setelah Penyesuaian</label>
                                    <input type="text" id="newStock" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Simpan Penyesuaian</button>
                            <a href="{{ route('stock-adjustments.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('specificpagescripts')
<script>
let products = [];
@foreach($products as $p)
    products[{{ $p->id }}] = {{ $p->stock }};
@endforeach

$(document).on('change', 'select[name="product_id"]', function() {
    const productId = $(this).val();
    const stock = products[productId] || 0;
    $('#currentStock').val(stock);
    calculateNewStock();
});

$(document).on('input', 'input[name="quantity"]', function() {
    calculateNewStock();
});

$(document).on('change', 'input[name="adjustment_type"]', function() {
    calculateNewStock();
});

function calculateNewStock() {
    const stock = parseInt($('#currentStock').val()) || 0;
    const quantity = parseInt($('input[name="quantity"]').val()) || 0;
    const type = $('input[name="adjustment_type"]:checked').val();
    
    let newStock;
    if (type === 'increase') {
        newStock = stock + quantity;
    } else {
        newStock = stock - quantity;
    }
    
    $('#newStock').val(newStock);
}

// Initialize
calculateNewStock();
</script>
@endsection
@endsection
