@extends('dashboard.body.main')

@section('title', 'Tutup Kasir Harian')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Tutup Kasir Harian</h5>
                    <a href="{{ route('cash-closings.create') }}" class="btn btn-primary btn-sm mt-2 mt-sm-0">+ Buat Tutup Kasir</a>
                </div>
                <div class="card-body">
                    <!-- Filter -->
                    <form method="GET" class="inventory-filter">
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" name="date" class="form-control" value="{{ request('date', now()->toDateString()) }}">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Tutup</option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                            </select>
                        </div>
                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('cash-closings.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Total Sales</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($closings as $closing)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $closing->closing_date }}</td>
                                        <td>{{ $closing->closing_time->format('H:i') }}</td>
                                        <td>Rp {{ number_format($closing->total_sales, 0) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $closing->status == 'verified' ? 'success' : 'warning' }}">
                                                {{ $closing->status == 'verified' ? 'Verified' : 'Tutup' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-action">
                                                <a href="{{ route('cash-closings.show', $closing->id) }}" class="btn btn-sm btn-info">Lihat</a>
                                                <a href="{{ route('cash-closings.print', $closing->id) }}" target="_blank" class="btn btn-sm btn-secondary">Cetak</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Data tidak ditemukan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $closings->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
