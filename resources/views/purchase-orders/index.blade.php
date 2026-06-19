@extends('dashboard.body.main')

@section('title', 'Order Pembelian')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Order Pembelian</h5>
                    <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary btn-sm mt-2 mt-sm-0">+ Tambah PO</a>
                </div>
                <div class="card-body">
                    <form method="GET" class="inventory-filter">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Batal</option>
                            </select>
                        </div>
                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No. PO</th>
                                    <th>Tanggal PO</th>
                                    <th>Pemasok</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchaseOrders as $po)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $po->po_number }}</td>
                                        <td>{{ $po->po_date }}</td>
                                        <td>{{ $po->supplier->name ?? '-' }}</td>
                                        <td>Rp {{ number_format($po->total, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $po->status == 'pending' ? 'warning' : 
                                                ($po->status == 'completed' ? 'success' : 'danger')
                                            }}">
                                                {{ 
                                                    $po->status == 'pending' ? 'Pending' : 
                                                    ($po->status == 'completed' ? 'Selesai' : 'Batal')
                                                }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-action">
                                            <a href="{{ route('purchase-orders.show', $po->id) }}" class="btn btn-sm btn-info">Lihat</a>
                                            @if($po->status == 'pending')
                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#cancelModal{{ $po->id }}">Batal</button>
                                            @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Cancel Modal -->
                                    <div class="modal fade" id="cancelModal{{ $po->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Batal PO {{ $po->po_number }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('purchase-orders.cancel', $po->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Alasan Pembatalan</label>
                                                            <textarea name="cancel_reason" class="form-control" required rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger">Konfirmasi Batal</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Data tidak ditemukan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $purchaseOrders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
