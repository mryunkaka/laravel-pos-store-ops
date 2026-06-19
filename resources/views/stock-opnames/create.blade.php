@extends('dashboard.body.main')

@section('title', 'Buat Stock Opname')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Buat Stock Opname</h5>
                    <a href="{{ route('stock-opnames.index') }}" class="btn btn-secondary btn-sm mt-2 mt-sm-0">Kembali</a>
                </div>
                <div class="card-body">
                    @if (session()->has('error'))
                        <div class="alert text-white bg-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('stock-opnames.store') }}" method="POST">
                        @csrf
                        <div class="alert alert-info">
                            Sistem akan membuat daftar opname dari semua produk aktif saat ini. Total produk: <strong>{{ $productCount }}</strong>.
                        </div>
                        <div class="form-group">
                            <label>Tanggal Opname</label>
                            <input type="date" name="opname_date" class="form-control @error('opname_date') is-invalid @enderror" value="{{ old('opname_date', now()->toDateString()) }}" required>
                            @error('opname_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                        </div>
                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Buat Batch</button>
                            <a href="{{ route('stock-opnames.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
