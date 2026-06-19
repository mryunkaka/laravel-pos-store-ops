@extends('dashboard.body.main')

@section('title', 'Import Hasil Opname')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Import Hasil Opname {{ $stockOpname->opname_number }}</h5>
                    <a href="{{ route('stock-opnames.show', $stockOpname->id) }}" class="btn btn-secondary btn-sm mt-2 mt-sm-0">Kembali</a>
                </div>
                <div class="card-body">
                    @if (session()->has('error'))
                        <div class="alert text-white bg-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    <div class="alert alert-info">
                        Format Excel: kolom A = kode produk, kolom B = nama produk opsional, kolom C = stok fisik, kolom D = catatan. Baris pertama digunakan sebagai header.
                    </div>

                    <form action="{{ route('stock-opnames.import.store', $stockOpname->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>File Excel</label>
                            <input type="file" name="upload_file" class="form-control @error('upload_file') is-invalid @enderror" accept=".xls,.xlsx" required>
                            @error('upload_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Import</button>
                            <a href="{{ route('stock-opnames.show', $stockOpname->id) }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
