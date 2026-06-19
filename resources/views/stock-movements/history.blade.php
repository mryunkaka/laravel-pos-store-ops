@extends('dashboard.body.main')

@section('title', 'Riwayat Stok - ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        Riwayat Stok: {{ $product->name }}
                        <span class="badge bg-primary ms-2">Stok: {{ $product->stock }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Summary Card -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Stok Masuk</h6>
                                    <p class="card-text fs-5">{{ $summary['in'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Stok Keluar</h6>
                                    <p class="card-text fs-5">{{ $summary['out'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Adjustment Masuk</h6>
                                    <p class="card-text fs-5">{{ $summary['adjustment_in'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h6 class="card-title">Adjustment Keluar</h6>
                                    <p class="card-text fs-5">{{ $summary['adjustment_out'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter -->
                    <form method="GET" class="mb-3 row g-2">
                        <div class="col-md-3">
                            <select name="type" class="form-select">
                                <option value="">Semua Tipe</option>
                                <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Stok Masuk</option>
                                <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Stok Keluar</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="start_date" class="form-control" placeholder="Dari Tanggal" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="end_date" class="form-control" placeholder="Sampai Tanggal" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                            <a href="{{ route('stock-movements.history', $product->id) }}" class="btn btn-secondary w-100 mt-1">Reset</a>
                        </div>
                    </form>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Tipe</th>
                                    <th>Quantity</th>
                                    <th>Harga</th>
                                    <th>Deskripsi</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements as $movement)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $movement->type == 'in' ? 'success' : 
                                                ($movement->type == 'out' ? 'danger' : 
                                                ($movement->type == 'adjustment_in' ? 'info' : 'warning'))
                                            }}">
                                                {{ 
                                                    $movement->type == 'in' ? 'Masuk' : 
                                                    ($movement->type == 'out' ? 'Keluar' : 
                                                    ($movement->type == 'adjustment_in' ? 'Adj. Masuk' : 'Adj. Keluar'))
                                                }}
                                            </span>
                                        </td>
                                        <td>{{ $movement->quantity }}</td>
                                        <td>Rp {{ number_format($movement->unit_price, 0, ',', '.') }}</td>
                                        <td>{{ $movement->description ?? '-' }}</td>
                                        <td>{{ $movement->user->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Data tidak ditemukan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $movements->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
