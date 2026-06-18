@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <!-- Alert: Session Status -->
                @if (session()->has('success'))
                    <div class="alert text-white bg-success" role="alert">
                        <div class="iq-alert-text">{{ session('success') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                @endif

                <!-- Section: Header and Add Button -->
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="mb-3">Daftar Pelanggan</h4>
                        <p class="mb-0">Dashboard pelanggan memungkinkan Anda dengan mudah mengumpulkan dan memvisualisasikan data pelanggan <br>
                            untuk mengoptimalkan pengalaman pelanggan dan menjaga retensi pelanggan.</p>
                    </div>
                    <div>
                        <a href="{{ route('customers.create') }}" class="btn btn-primary add-list">
                            <x-heroicon-o-plus class="w-5 h-5 mr-3" />Tambah Pelanggan
                        </a>
                        <a href="{{ route('customers.index') }}" class="btn btn-danger add-list">
                            <x-heroicon-o-x-mark class="w-5 h-5 mr-3" />Bersihkan Pencarian
                        </a>
                    </div>
                </div>
            </div>

            <!-- Section: Filters and Search -->
            <div class="col-lg-12">
                <form action="{{ route('customers.index') }}" method="get">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <!-- Row Per Page Checkbox -->
                        <div class="form-group row">
                            <label for="row" class="col-sm-3 align-self-center">Baris:</label>
                            <div class="col-sm-9">
                                <select class="form-control" name="row" onchange="this.form.submit()">
                                    <option value="10" @if (request('row') == '10') selected="selected" @endif>10</option>
                                    <option value="25" @if (request('row') == '25') selected="selected" @endif>25</option>
                                    <option value="50" @if (request('row') == '50') selected="selected" @endif>50</option>
                                    <option value="100" @if (request('row') == '100') selected="selected" @endif>100</option>
                                </select>
                            </div>
                        </div>

                        <!-- Search Box -->
                        <div class="form-group row">
                            <label class="control-label col-sm-3 align-self-center" for="search">Cari:</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <input type="text" id="search" class="form-control" name="search"
                                        placeholder="Cari pelanggan" value="{{ request('search') }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="input-group-text bg-primary">
                                            <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Section: Customer Table -->
            <div class="col-lg-12">
                <div class="table-responsive rounded mb-3">
                    <table class="table mb-0">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">
                                <th>No.</th>
                                <th><x-sort-link name="name" label="Nama" /></th>
                                <th><x-sort-link name="email" label="Email" /></th>
                                <th><x-sort-link name="phone" label="Telepon" /></th>
                                <th><x-sort-link name="city" label="Kota" /></th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="ligth-body">
                            @foreach ($customers as $customer)
                                <tr>
                                    <td>{{ (($customers->currentPage() * 10) - 10) + $loop->iteration }}</td>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->email }}</td>
                                    <td>{{ $customer->phone }}</td>
                                    <td>{{ $customer->city }}</td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center list-action">
                                            <a class="btn btn-info mr-2" data-toggle="tooltip" data-placement="top" title="Lihat"
                                                href="{{ route('customers.show', $customer->id) }}">
                                                <x-heroicon-o-eye class="w-5 h-5 mr-0" />
                                            </a>
                                            <a class="btn btn-success mr-2" data-toggle="tooltip" data-placement="top" title="Ubah"
                                                href="{{ route('customers.edit', $customer->id) }}">
                                                <x-heroicon-o-pencil class="w-5 h-5 mr-0" />
                                            </a>
                                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST"
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
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Section: Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
