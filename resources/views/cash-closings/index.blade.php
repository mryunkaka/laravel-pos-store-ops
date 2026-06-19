@extends('dashboard.body.main')

@section('title', 'Tutup Kasir Harian')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tutup Kasir Harian</h5>
                    <a href="{{ route('cash-closings.create') }}" class="btn btn-primary btn-sm">+ Buat Tutup Kasir</a>
                </div>
                <div class="card-body">
                    <!-- Filter -->
                    <form method="GET" class="mb-3 row g-2">
                        <div class="col-md-3">
                            <input type="date" name="date" class="form-control" value="{{ request('date', now()->toDateString()) }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Tutup</option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('cash-closings.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                                            <a href="{{ route('cash-closings.show', $closing->id) }}" class="btn btn-sm btn-info">View</a>
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