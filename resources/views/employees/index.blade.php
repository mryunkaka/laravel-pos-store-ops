@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <!-- Alert: Success Message -->
                @if (session()->has('success'))
                    <div class="alert text-white bg-success" role="alert">
                        <div class="iq-alert-text">{{ session('success') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                @endif

                <!-- Header: Page Title and Add Button -->
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="mb-3">Daftar Karyawan</h4>
                        <p class="mb-0">Kelola semua karyawan Anda dari satu tempat. <br> Lihat, buat, perbarui, atau hapus
                            data karyawan.</p>
                    </div>
                    <div>
                        <a href="{{ route('employees.create') }}" class="btn btn-primary add-list">
                            <x-heroicon-o-plus class="w-5 h-5 mr-3" />Tambah Karyawan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filter: Search and Pagination -->
            <div class="col-lg-12">
                <form action="{{ route('employees.index') }}" method="get">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div class="form-group row">
                            <label for="row" class="col-sm-3 align-self-center">Baris:</label>
                            <div class="col-sm-9">
                                <select class="form-control" name="row" onchange="this.form.submit()">
                                    <option value="10" @if (request('row') == '10') selected="selected" @endif>10
                                    </option>
                                    <option value="25" @if (request('row') == '25') selected="selected" @endif>25
                                    </option>
                                    <option value="50" @if (request('row') == '50') selected="selected" @endif>50
                                    </option>
                                    <option value="100" @if (request('row') == '100') selected="selected" @endif>100
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-12">
                                <div class="input-group">
                                    <input type="text" id="search" class="form-control" name="search"
                                        placeholder="Cari berdasarkan nama atau email..." value="{{ request('search') }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="input-group-text bg-primary">
                                            <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                                        </button>
                                    </div>
                                    @if (request('search'))
                                        <div class="input-group-append">
                                            <a href="{{ route('employees.index') }}" class="input-group-text bg-danger"
                                                title="Bersihkan Pencarian"
                                                <x-heroicon-o-x-mark class="w-5 h-5" />
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table: Employee Records -->
            <div class="col-lg-12">
                <div class="table-responsive rounded mb-3">
                    <table class="table mb-0">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">
                                <th>No.</th>
                                <th>Foto</th>
                                <th><x-sort-link name="name" label="Nama" /></th>
                                <th><x-sort-link name="email" label="Email" /></th>
                                <th><x-sort-link name="phone" label="Telepon" /></th>
                                <th><x-sort-link name="salary" label="Gaji" /></th>
                                <th><x-sort-link name="city" label="Kota" /></th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="ligth-body">
                            @forelse ($employees as $employee)
                                <tr>
                                    <td>{{ (($employees->currentPage() * $employees->perPage()) - $employees->perPage()) + $loop->iteration }}
                                    </td>
                                    <td>
                                        <img class="avatar-60 rounded"
                                            src="{{ $employee->photo ? asset('storage/employees/' . $employee->photo) : asset('assets/images/user/1.png') }}"
                                            style="object-fit: cover;">
                                    </td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->email }}</td>
                                    <td>{{ $employee->phone }}</td>
                                    <td>${{ number_format($employee->salary, 2) }}</td>
                                    <td>{{ $employee->city }}</td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center list-action">
                                            <a class="btn btn-info mr-2" data-toggle="tooltip" data-placement="top"
                                                title="Lihat" href="{{ route('employees.show', $employee->id) }}">
                                                <x-heroicon-o-eye class="w-5 h-5 mr-0" />
                                            </a>
                                            <a class="btn btn-success mr-2" data-toggle="tooltip" data-placement="top"
                                                title="Ubah" href="{{ route('employees.edit', $employee->id) }}">
                                                <x-heroicon-o-pencil class="w-5 h-5 mr-0" />
                                            </a>
                                            <form action="{{ route('employees.destroy', $employee->id) }}" method="POST"
                                                style="display:inline;">
                                                @method('delete')
                                                @csrf
                                                <button type="submit" class="btn btn-warning border-0"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
                                                    data-toggle="tooltip" data-placement="top" title="Hapus">
                                                    <x-heroicon-o-trash class="w-5 h-5 mr-0" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="alert text-white bg-warning mt-2" role="alert">
                                            <div class="iq-alert-text">Tidak ada data ditemukan.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                {{ $employees->links() }}
            </div>
        </div>
    </div>
@endsection
