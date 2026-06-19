@extends('dashboard.body.main')

@section('title', 'Adjustment Stok - ' . $product->name)

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Adjustment Stok: {{ $product->name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('stock-movements.adjust') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <div class="mb-3">
                            <label class="form-label">Produk</label>
                            <input type="text" class="form-control" value="{{ $product->name }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jumlah Adjustment</label>
                            <input type="number" name="quantity" class="form-control" required min="1" placeholder="Jumlah adjustment">
                            <div class="form-text">Masukkan jumlah positif untuk tambah stok, atau gunakan form adjustment keluar</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alasan Adjustment</label>
                            <textarea name="reason" class="form-control" required rows="3" placeholder="Jelaskan alasan adjustment stok"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipe Adjustment</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="adjust_type" id="adjIn" value="in" checked>
                                <label class="form-check-label" for="adjIn">
                                    <span class="badge bg-success">Stok Masuk</span> - Tambah stok
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="adjust_type" id="adjOut" value="out">
                                <label class="form-check-label" for="adjOut">
                                    <span class="badge bg-warning">Stok Keluar</span> - Kurangi stok
                                </label>
                            </div>
                        </div>

                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Simpan Penyesuaian</button>
                            <a href="{{ route('stock-movements.history', $product->id) }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
