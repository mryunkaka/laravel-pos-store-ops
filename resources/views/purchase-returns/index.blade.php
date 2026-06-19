@extends('dashboard.body.main')

@section('title', 'Retur Pembelian')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Retur Pembelian</h5>
                    <a href="{{ route('purchase-returns.create') }}" class="btn btn-primary btn-sm mt-2 mt-sm-0">+ Tambah Retur</a>
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
                            <a href="{{ route('purchase-returns.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No. Retur</th>
                                    <th>Tanggal</th>
                                    <th>Penerimaan</th>
                                    <th>Pemasok</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($returns as $return)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $return->return_number }}</td>
                                        <td>{{ $return->return_date }}</td>
                                        <td>{{ $return->purchaseReceiving->receiving_number ?? '-' }}</td>
                                        <td>{{ $return->supplier->name ?? '-' }}</td>
                                        <td>Rp {{ number_format($return->grand_total, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $return->status == 'pending' ? 'warning' : 'success'
                                            }}">
                                                {{ 
                                                    $return->status == 'pending' ? 'Pending' : 'Selesai'
                                                }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-action">
                                            <a href="{{ route('purchase-returns.show', $return->id) }}" class="btn btn-sm btn-info">Lihat</a>
                                            @if($return->status == 'pending')
                                                <form action="{{ route('purchase-returns.complete', $return->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Selesaikan retur ini?')">Selesai</button>
                                                </form>
                                                <form action="{{ route('purchase-returns.destroy', $return->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus retur ini?')">Hapus</button>
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
                    {{ $returns->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
