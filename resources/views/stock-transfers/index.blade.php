@extends('dashboard.body.main')

@section('title', 'Transfer Stok')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @if (session()->has('success'))
                <div class="alert text-white bg-success" role="alert">
                    <div class="iq-alert-text">{{ session('success') }}</div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <x-heroicon-o-x-mark class="w-6 h-6"/>
                    </button>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert text-white bg-danger" role="alert">
                    <div class="iq-alert-text">{{ session('error') }}</div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <x-heroicon-o-x-mark class="w-6 h-6"/>
                    </button>
                </div>
            @endif

            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Transfer Stok</h5>
                    <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary btn-sm mt-2 mt-sm-0">+ Tambah Transfer</a>
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
                            <a href="{{ route('stock-transfers.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No. Transfer</th>
                                    <th>Tanggal</th>
                                    <th>Dari</th>
                                    <th>Ke</th>
                                    <th>Status</th>
                                    <th>Dibuat oleh</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transfers as $transfer)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $transfer->transfer_number }}</td>
                                        <td>{{ $transfer->transfer_date }}</td>
                                        <td>{{ $transfer->fromLocation->name ?? '-' }}</td>
                                        <td>{{ $transfer->toLocation->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $transfer->status === 'pending' ? 'warning' : ($transfer->status === 'completed' ? 'success' : 'danger') }}">
                                                {{ $transfer->status === 'pending' ? 'Pending' : ($transfer->status === 'completed' ? 'Selesai' : 'Batal') }}
                                            </span>
                                        </td>
                                        <td>{{ $transfer->creator->name ?? '-' }}</td>
                                        <td>
                                            <div class="table-action">
                                                <a href="{{ route('stock-transfers.show', $transfer->id) }}" class="btn btn-sm btn-info">Lihat</a>
                                                @if($transfer->status === 'pending')
                                                    <form action="{{ route('stock-transfers.complete', $transfer->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Selesaikan transfer stok ini?')">Selesai</button>
                                                    </form>
                                                    <form action="{{ route('stock-transfers.destroy', $transfer->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus transfer stok ini?')">Hapus</button>
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

                    {{ $transfers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
