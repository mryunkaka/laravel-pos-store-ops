@extends('dashboard.body.main')

@section('title', 'Shift Kasir')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Shift Kasir</h5>
                    <a href="{{ route('cash-shifts.create') }}" class="btn btn-primary btn-sm">+ Buka Shift Baru</a>
                </div>
                <div class="card-body">
                    <!-- Filter -->
                    <form method="GET" class="mb-3 row g-2">
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Tutup</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="start_date" class="form-control" placeholder="Dari" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="end_date" class="form-control" placeholder="Sampai" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('cash-shifts.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Shift</th>
                                    <th>Kasir</th>
                                    <th>Kas Awal</th>
                                    <th>Kas Akhir</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shifts as $shift)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $shift->start_time->format('Y-m-d') }}</td>
                                        <td>{{ $shift->start_time->format('H:i') }}</td>
                                        <td>{{ $shift->user->name ?? '-' }}</td>
                                        <td>Rp {{ number_format($shift->opening_balance, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($shift->closing_balance, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $shift->status == 'active' ? 'success' : 'warning'
                                            }}">
                                                {{ 
                                                    $shift->status == 'active' ? 'Aktif' : 'Tutup'
                                                }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('cash-shifts.show', $shift->id) }}" class="btn btn-sm btn-info">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Data tidak ditemukan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $shifts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection