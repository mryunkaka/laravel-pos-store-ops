@extends('dashboard.body.main')

@section('title', 'Stock Movements')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Pergerakan Stok</h5>
                </div>
                <div class="card-body">
                    <!-- Filter -->
                    <form method="GET" class="mb-3 row g-2">
                        <div class="col-md-3">
                            <select name="type" class="form-select">
                                <option value="">Semua Tipe</option>
                                <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Stok Masuk</option>
                                <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Stok Keluar</option>
                                <option value="adjustment_in" {{ request('type') == 'adjustment_in' ? 'selected' : '' }}>Adjustment Masuk</option>
                                <option value="adjustment_out" {{ request('type') == 'adjustment_out' ? 'selected' : '' }}>Adjustment Keluar</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="product_id" class="form-select">
                                <option value="">Semua Produk</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="start_date" class="form-control" placeholder="Dari Tanggal" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="end_date" class="form-control" placeholder="Sampai Tanggal" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('stock-movements.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Produk</th>
                                    <th>Tipe</th>
                                    <th>Quantity</th>
                                    <th>Harga</th>
                                    <th>Referensi</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements as $movement)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                        <td>{{ $movement->product->name ?? '-' }}</td>
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
                                        <td>{{ $movement->reference_type ?? '-' }}</td>
                                        <td>{{ $movement->user->name ?? '-' }}</td>
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
                    {{ $movements->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
