@extends('dashboard.body.main')

@section('title', 'Riwayat Stok - ' . $product->name)

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        Riwayat Stok: {{ $product->name }}
                        <span class="badge bg-primary ml-2">Stok: {{ $product->stock }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Summary Card -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="bg-success text-white rounded p-3 mb-2">
                                <div class="card-body">
                                    <h6 class="card-title">Stok Masuk</h6>
                                    <p class="card-text h5 mb-0">{{ $summary['in'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-danger text-white rounded p-3 mb-2">
                                <div class="card-body">
                                    <h6 class="card-title">Stok Keluar</h6>
                                    <p class="card-text h5 mb-0">{{ $summary['out'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-info text-white rounded p-3 mb-2">
                                <div class="card-body">
                                    <h6 class="card-title">Adjustment Masuk</h6>
                                    <p class="card-text h5 mb-0">{{ $summary['adjustment_in'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-warning text-dark rounded p-3 mb-2">
                                <div class="card-body">
                                    <h6 class="card-title">Adjustment Keluar</h6>
                                    <p class="card-text h5 mb-0">{{ $summary['adjustment_out'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="GET" class="inventory-filter">
                        <div class="form-group">
                            <label>Tipe</label>
                            <select name="type" class="form-control">
                                <option value="">Semua Tipe</option>
                                <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Stok Masuk</option>
                                <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Stok Keluar</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" placeholder="Dari Tanggal" value="{{ request('start_date') }}">
                        </div>
                        <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" placeholder="Sampai Tanggal" value="{{ request('end_date') }}">
                        </div>
                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('stock-movements.history', $product->id) }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
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
