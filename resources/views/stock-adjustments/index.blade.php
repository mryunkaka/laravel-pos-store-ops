@extends('dashboard.body.main')

@section('title', 'Penyesuaian Stok')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Penyesuaian Stok</h5>
                    <a href="{{ route('stock-adjustments.create') }}" class="btn btn-primary btn-sm mt-2 mt-sm-0">+ Tambah Penyesuaian</a>
                </div>
                <div class="card-body">
                    <form method="GET" class="inventory-filter">
                        <div class="form-group">
                            <label>Produk</label>
                            <select name="product_id" class="form-control">
                                <option value="">Semua Produk</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
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
                            <a href="{{ route('stock-adjustments.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Produk</th>
                                    <th>Tipe</th>
                                    <th>Qty</th>
                                    <th>Stok Lama</th>
                                    <th>Stok Baru</th>
                                    <th>Alasan</th>
                                    <th>Dibuat oleh</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adjustments as $adjustment)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $adjustment->adjustment_date }}</td>
                                        <td>{{ $adjustment->product->name }}</td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $adjustment->adjustment_type == 'increase' ? 'success' : 'danger'
                                            }}">
                                                {{ 
                                                    $adjustment->adjustment_type == 'increase' ? 'Tambah' : 'Kurang'
                                                }}
                                            </span>
                                        </td>
                                        <td>{{ $adjustment->quantity }}</td>
                                        <td>{{ $adjustment->old_stock }}</td>
                                        <td>{{ $adjustment->new_stock }}</td>
                                        <td>{{ $adjustment->reason }}</td>
                                        <td>{{ $adjustment->user->name ?? '-' }}</td>
                                        <td>
                                            <div class="table-action">
                                            <a href="{{ route('stock-adjustments.show', $adjustment->id) }}" class="btn btn-sm btn-info">Lihat</a>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteAdjustment({{ $adjustment->id }})">Hapus</button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">Data tidak ditemukan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $adjustments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@section('specificpagescripts')
<script>
function deleteAdjustment(id) {
    if (confirm('Hapus penyesuaian ini?')) {
        $.post('{{ route("stock-adjustments.destroy", "") }}/' + id, {
            _token: '{{ csrf_token() }}',
            _method: 'DELETE'
        }).done(function() {
            location.reload();
        });
    }
}
</script>
@endsection
@endsection
