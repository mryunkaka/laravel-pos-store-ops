@extends('dashboard.body.main')

@section('title', 'Backup Database')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Backup Database</h5>
                    <form method="POST" action="{{ route('backup.create') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">Buat Backup</button>
                    </form>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert text-white bg-success" role="alert">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert text-white bg-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    @can('restore-database')
                        <div class="border rounded p-3 mb-4 bg-light">
                            <form method="POST" action="{{ route('backup.restore') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row align-items-end">
                                    <div class="form-group col-lg-8 mb-lg-0">
                                        <label>File Restore (.sql/.zip)</label>
                                        <input type="file" name="backup_file" class="form-control @error('backup_file') is-invalid @enderror" accept=".sql,.txt,.zip" required>
                                        @error('backup_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group col-lg-4 mb-0 d-flex justify-content-lg-end">
                                        <button type="submit" class="btn btn-warning" onclick="return confirm('Restore akan menimpa data database saat ini. Lanjutkan?')">Restore Database</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endcan

                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>File</th>
                                    <th>Ukuran</th>
                                    <th>Diubah</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($files as $file)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $file->getFilename() }}</td>
                                        <td>{{ number_format($file->getSize() / 1024, 2) }} KB</td>
                                        <td>{{ date('Y-m-d H:i', $file->getMTime()) }}</td>
                                        <td>
                                            <div class="d-flex flex-wrap" style="gap: 8px;">
                                                <a href="{{ route('backup.download', $file->getFilename()) }}" class="btn btn-sm btn-success">Download</a>
                                                <form method="POST" action="{{ route('backup.delete', $file->getFilename()) }}" class="d-inline" onsubmit="return confirm('Hapus backup ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Backup belum tersedia</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
