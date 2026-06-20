@extends('dashboard.body.main')

@section('title', 'Audit Log')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Audit Log</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="inventory-filter">
                        <div class="form-group">
                            <label>Pengguna</label>
                            <select name="user_id" class="form-control">
                                <option value="">Semua Pengguna</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Modul</label>
                            <select name="module" class="form-control">
                                <option value="">Semua Modul</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>{{ $module }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Aksi</label>
                            <select name="action" class="form-control">
                                <option value="">Semua Aksi</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $action }}</option>
                                @endforeach
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
                            <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Waktu</th>
                                    <th>Pengguna</th>
                                    <th>Modul</th>
                                    <th>Aksi</th>
                                    <th>Deskripsi</th>
                                    <th>IP</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>{{ $logs->firstItem() + $loop->index }}</td>
                                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>{{ $log->user->name ?? '-' }}</td>
                                        <td><span class="badge bg-primary">{{ $log->module }}</span></td>
                                        <td>{{ $log->action }}</td>
                                        <td>{{ $log->description }}</td>
                                        <td>{{ $log->ip_address }}</td>
                                        <td>
                                            <a href="{{ route('audit-logs.show', $log->id) }}" class="btn btn-sm btn-info">Lihat</a>
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

                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
