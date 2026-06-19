@extends('dashboard.body.main')

@section('title', 'Penerimaan Barang')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Penerimaan Barang</h5>
                    <a href="{{ route('purchase-receivings.create') }}" class="btn btn-primary btn-sm mt-2 mt-sm-0">+ Tambah Penerimaan</a>
                </div>
                <div class="card-body">
                    <form method="GET" class="inventory-filter">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('purchase-receivings.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No. Penerimaan</th>
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
                                            <div class="table-action">
                                            <a href="{{ route('purchase-receivings.show', $receiving->id) }}" class="btn btn-sm btn-info">Lihat</a>
                                            @if($receiving->status == 'pending')
                                                <form action="{{ route('purchase-receivings.complete', $receiving->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Selesaikan penerimaan ini?')">Selesai</button>
                                                </form>
                                            @endif
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
                    {{ $receivings->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
