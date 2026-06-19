@extends('dashboard.body.main')

@section('title', 'Buka Shift Kasir')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Buka Shift Kasir</h5>
                    <a href="{{ route('cash-shifts.index') }}" class="btn btn-secondary btn-sm mt-2 mt-sm-0">Kembali</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('cash-shifts.store') }}" method="POST" id="shiftForm">
                        @csrf
                        <div class="mb-3">
                            <div class="alert alert-info">
                                <strong>Catatan:</strong> Pastikan Anda telah mempersiapkan kas awal sebelum membuka shift.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kas Awal (Rp)</label>
                            <input type="number" name="opening_balance" class="form-control @error('opening_balance') is-invalid @enderror" value="{{ old('opening_balance', 0) }}" min="0" step="1000" required>
                            @error('opening_balance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Masukkan jumlah kas fisik yang ada di kasir</small>
                        </div>

                        <div class="mb-3">
                            <div class="alert alert-warning">
                                <strong>Peringatan:</strong> Jangan tutup browser atau refresh halaman sampai shift ditutup secara resmi.
                            </div>
                        </div>

                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Buka Shift</button>
                            <a href="{{ route('cash-shifts.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
