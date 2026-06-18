@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                @if (session()->has('success'))
                    <div class="alert text-white bg-success" role="alert">
                        <div class="iq-alert-text">{{ session('success') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                        </div>
                @endif
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Daftar Role</h4>
                            </div>
                            <div>
                                <a href="{{ route('role.create') }}" class="btn btn-primary add-list"><x-heroicon-o-plus
                                        class="w-5 h-5 mr-3" />Tambah Role</a>
                            </div>
                        </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive rounded mb-3">
                                        <table class="table mb-0">
                                            <thead class="bg-white text-uppercase">
                                                <tr class="ligth ligth-data">
                                                    <th>No.</th>
                                                    <th>Nama Role</th>
                                                    <th>Dibuat Pada</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="ligth-body">
                                                @forelse ($roles as $role)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $role->name }}</td>
                                                        <td>{{ $role->created_at->format('d M Y') }}</td>
                                                        <td>
                                                            <div class="d-flex align-items-center list-action">
                                                                <a class="btn btn-success mr-2" data-toggle="tooltip" data-placement="top"
                                                                    title="Ubah" href="{{ route('role.edit', $role->id) }}">
                                                                    <x-heroicon-o-pencil class="w-5 h-5 mr-0" />
                                                                </a>
                                                                <form action="{{ route('role.destroy', $role->id) }}" method="POST"
                                                                    style="display:inline;">
                                                                    @method('delete')
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-warning border-0"
                                                                        onclick="return confirm('Apakah Anda yakin ingin menghapus role ini?')"
                                                                        data-toggle="tooltip" data-placement="top" title="Hapus">
                                                                        <x-heroicon-o-trash class="w-5 h-5 mr-0" />
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center">
                                                            <div class="alert text-white bg-danger" role="alert">
                                                                <div class="iq-alert-text">Data tidak Ditemukan.</div>
                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                    <x-heroicon-o-x-mark class="w-5 h-5" />
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($roles->hasPages())
                                        {{ $roles->links() }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        </div>
                        </div>
@endsection
