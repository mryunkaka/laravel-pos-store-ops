@extends('dashboard.body.main')

@section('title', 'Detail Audit Log')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Detail Audit Log</h5>
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><strong>Waktu:</strong> {{ $auditLog->created_at->format('Y-m-d H:i:s') }}</div>
                        <div class="col-md-6 mb-3"><strong>Pengguna:</strong> {{ $auditLog->user->name ?? '-' }}</div>
                        <div class="col-md-6 mb-3"><strong>Modul:</strong> {{ $auditLog->module }}</div>
                        <div class="col-md-6 mb-3"><strong>Aksi:</strong> {{ $auditLog->action }}</div>
                        <div class="col-md-6 mb-3"><strong>IP:</strong> {{ $auditLog->ip_address }}</div>
                        <div class="col-md-6 mb-3"><strong>Referensi:</strong> {{ $auditLog->reference_type }} #{{ $auditLog->reference_id }}</div>
                    </div>

                    <div class="mb-3">
                        <strong>Deskripsi:</strong>
                        <p class="mb-0">{{ $auditLog->description }}</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Data Lama</h6>
                            <pre class="p-3 bg-light border">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                        <div class="col-md-6">
                            <h6>Data Baru</h6>
                            <pre class="p-3 bg-light border">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>

                    <div class="mt-3">
                        <strong>User Agent:</strong>
                        <p class="mb-0">{{ $auditLog->user_agent }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
