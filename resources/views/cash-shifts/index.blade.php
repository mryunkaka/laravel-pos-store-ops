@extends('dashboard.body.main')

@section('title', 'Shift Kasir')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Shift Kasir</h5>
                    <a href="{{ route('cash-shifts.create') }}" class="btn btn-primary btn-sm mt-2 mt-sm-0">+ Buka Shift Baru</a>
                </div>
                <div class="card-body">
                    <!-- Filter -->
                    <form method="GET" class="inventory-filter">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Tutup</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" placeholder="Dari" value="{{ request('start_date') }}">
                        </div>
                        <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" placeholder="Sampai" value="{{ request('end_date') }}">
                        </div>
                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('cash-shifts.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
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
                                            <div class="table-action">
                                                <a href="{{ route('cash-shifts.show', $shift->id) }}" class="btn btn-sm btn-info">Lihat</a>
                                                <a href="{{ route('cash-shifts.print', $shift->id) }}" target="_blank" class="btn btn-sm btn-secondary">Cetak</a>
                                            </div>
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
