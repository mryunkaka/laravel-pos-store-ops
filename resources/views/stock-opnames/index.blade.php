@extends('dashboard.body.main')

@section('title', 'Stock Opname')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Stock Opname</h5>
                    <a href="{{ route('stock-opnames.create') }}" class="btn btn-primary btn-sm mt-2 mt-sm-0">+ Buat Opname</a>
                </div>
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert text-white bg-success" role="alert">{{ session('success') }}</div>
                    @endif
                    @if (session()->has('error'))
                        <div class="alert text-white bg-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    <form method="GET" class="inventory-filter">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('stock-opnames.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No. Opname</th>
                                    <th>Tanggal</th>
                                    <th>Produk</th>
                                    <th>Status</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($opnames as $opname)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $opname->opname_number }}</td>
                                        <td>{{ $opname->opname_date->format('Y-m-d') }}</td>
                                        <td>{{ $opname->details_count }}</td>
                                        <td>
                                            <span class="badge bg-{{ $opname->status == 'approved' ? 'success' : ($opname->status == 'submitted' ? 'info' : ($opname->status == 'cancelled' ? 'danger' : 'warning')) }}">
                                                {{ ucfirst($opname->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $opname->creator->name ?? '-' }}</td>
                                        <td>
                                            <div class="table-action">
                                                <a href="{{ route('stock-opnames.show', $opname->id) }}" class="btn btn-sm btn-info">Lihat</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Data tidak ditemukan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $opnames->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
