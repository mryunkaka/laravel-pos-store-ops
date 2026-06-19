@extends('dashboard.body.main')

@section('title', 'Retur Pembelian')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Retur Pembelian</h5>
                    <a href="{{ route('purchase-returns.create') }}" class="btn btn-primary btn-sm">+ Tambah Retur</a>
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
                            <a href="{{ route('purchase-returns.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Retur Number</th>
                                    <th>Tanggal</th>
                                    <th>Receiving</th>
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
                                            <a href="{{ route('purchase-returns.show', $return->id) }}" class="btn btn-sm btn-info">View</a>
                                            @if($return->status == 'pending')
                                                <form action="{{ route('purchase-returns.destroy', $return->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus retur ini?')">Hapus</button>
                                                </form>
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
                    {{ $returns->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
