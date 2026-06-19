@extends('dashboard.body.main')

@section('title', 'Retur Penjualan')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Retur Penjualan</h5>
                    <a href="{{ route('sales-returns.create') }}" class="btn btn-primary btn-sm mt-2 mt-sm-0">+ Buat Retur</a>
                </div>
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert text-white bg-success" role="alert">{{ session('success') }}</div>
                    @endif
                    @if (session()->has('error'))
                        <div class="alert text-white bg-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    <form method="GET" class="inventory-filter">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('sales-returns.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No. Retur</th>
                                    <th>Invoice</th>
                                    <th>Tanggal</th>
                                    <th>Tipe</th>
                                    <th>Refund</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($returns as $return)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $return->return_number }}</td>
                                        <td>{{ $return->order->invoice_no ?? '-' }}</td>
                                        <td>{{ $return->return_date->format('Y-m-d') }}</td>
                                        <td>{{ $return->return_type === 'refund' ? 'Refund' : 'Tukar Barang' }}</td>
                                        <td>Rp {{ number_format($return->refund_amount, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $return->status == 'completed' ? 'success' : ($return->status == 'cancelled' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($return->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-action">
                                                <a href="{{ route('sales-returns.show', $return->id) }}" class="btn btn-sm btn-info">Lihat</a>
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

                    {{ $returns->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
