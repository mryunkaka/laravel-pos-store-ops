@extends('dashboard.body.main')

@section('title', 'Purchase Receivings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Penerimaan Barang</h5>
                    <a href="{{ route('purchase-receivings.create') }}" class="btn btn-primary btn-sm">+ Tambah Receiving</a>
                </div>
                <div class="card-body">
                    <!-- Filter -->
                    <form method="GET" class="mb-3 row g-2">
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('purchase-receivings.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Receiving Number</th>
                                    <th>Tanggal</th>
                                    <th>PO</th>
                                    <th>Pemasok</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($receivings as $receiving)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $receiving->receiving_number }}</td>
                                        <td>{{ $receiving->receiving_date }}</td>
                                        <td>{{ $receiving->purchaseOrder->po_number ?? '-' }}</td>
                                        <td>{{ $receiving->supplier->name ?? '-' }}</td>
                                        <td>Rp {{ number_format($receiving->total, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $receiving->status == 'pending' ? 'warning' : 'success'
                                            }}">
                                                {{ 
                                                    $receiving->status == 'pending' ? 'Pending' : 'Selesai'
                                                }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('purchase-receivings.show', $receiving->id) }}" class="btn btn-sm btn-info">View</a>
                                            @if($receiving->status == 'pending')
                                                <a href="{{ route('purchase-receivings.complete', $receiving->id) }}" class="btn btn-sm btn-success" onclick="return confirm('Selesaikan receiving ini?')">Selesai</a>
                                            @endif
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
                    {{ $receivings->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
