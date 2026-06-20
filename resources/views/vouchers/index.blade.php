@extends('dashboard.body.main')

@section('title', 'Voucher/Promo')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Voucher/Promo</h5>
                    <a href="{{ route('vouchers.create') }}" class="btn btn-primary btn-sm mt-2 mt-sm-0">+ Tambah Voucher</a>
                </div>
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert text-white bg-success" role="alert">{{ session('success') }}</div>
                    @endif
                    <form method="GET" class="inventory-filter">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                        <div class="inventory-actions">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('vouchers.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Tipe</th>
                                    <th>Diskon</th>
                                    <th>Periode</th>
                                    <th>Pakai</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($vouchers as $voucher)
                                    <tr>
                                        <td>{{ $voucher->code }}</td>
                                        <td>{{ $voucher->name }}</td>
                                        <td>{{ $voucher->type === 'percentage' ? 'Persen' : 'Nominal' }}</td>
                                        <td>{{ $voucher->type === 'percentage' ? $voucher->discount . '%' : 'Rp ' . number_format($voucher->discount, 0, ',', '.') }}</td>
                                        <td>{{ $voucher->start_date->format('Y-m-d') }} - {{ $voucher->end_date->format('Y-m-d') }}</td>
                                        <td>{{ $voucher->used_count }}{{ $voucher->max_use ? ' / ' . $voucher->max_use : '' }}</td>
                                        <td><span class="badge bg-{{ $voucher->is_active ? 'success' : 'secondary' }}">{{ $voucher->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                        <td>
                                            <div class="table-action">
                                                <a href="{{ route('vouchers.edit', $voucher->id) }}" class="btn btn-sm btn-info">Edit</a>
                                                <form action="{{ route('vouchers.destroy', $voucher->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Nonaktifkan</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center">Data tidak ditemukan</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $vouchers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
