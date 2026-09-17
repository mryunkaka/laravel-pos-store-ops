@extends('dashboard.body.main')

@section('container')
    <style>
        .row-selector-container {
            min-width: 0; /* Default for mobile - allows shrinking */
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .product-row-selected {
            background-color: #fff3cd;
        }

        @media (min-width: 576px) {
            .row-selector-container {
                min-width: 180px; /* Apply min-width only on sm+ screens */
                padding-top: 0;
                padding-bottom: 0;
            }
        }
    </style>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
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

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-3">Daftar Produk</h4>
                    <p class="mb-0">Dashboard produk memungkinkan Anda dengan mudah mengumpulkan dan memvisualisasikan data produk <br>
                        untuk mengoptimalkan pengalaman produk dan menjaga retensi produk.</p>
                </div>
                <div class="d-flex align-items-center">
                    <a href="{{ route('products.importView') }}" class="btn btn-success add-list mr-2 d-flex align-items-center">
                        <x-heroicon-o-arrow-up-tray class="w-5 h-5 mr-1" /> Import
                    </a>
                    <a href="{{ route('products.exportData') }}" class="btn btn-warning add-list mr-2 d-flex align-items-center">
                        <x-heroicon-o-arrow-down-tray class="w-5 h-5 mr-1" /> Export
                    </a>
                    <a href="{{ route('products.create') }}" class="btn btn-primary add-list d-flex align-items-center">
                        <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Tambah Produk
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <form action="{{ route('products.index') }}" method="get">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="form-group mb-0 mr-2 mt-n3 row-selector-container">
                        <div class="d-flex align-items-center">
                            <label for="row" class="mb-0 mr-2" style="min-width: 50px;">Baris:</label>
                            <select class="form-control" name="row">
                                <option value="10" @if(request('row') == '10')selected="selected"@endif>10</option>
                                <option value="25" @if(request('row') == '25')selected="selected"@endif>25</option>
                                <option value="50" @if(request('row') == '50')selected="selected"@endif>50</option>
                                <option value="100" @if(request('row') == '100')selected="selected"@endif>100</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-sm-3 align-self-center" for="search">Cari:</label>
                        <div class="input-group col-sm-8">
                            <input type="text" id="search" class="form-control" name="search" placeholder="Cari produk" value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="input-group-text bg-primary">
                                    <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                                </button>
                                <a href="{{ route('products.index') }}" class="input-group-text bg-danger">
                                    <x-heroicon-o-x-mark class="w-5 h-5" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-12">
            <form id="bulk-delete-form" action="{{ route('products.bulkDestroy') }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                    <div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="select-page-products">
                            <label class="custom-control-label" for="select-page-products">Pilih halaman ini</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="select-all-products">
                            <label class="custom-control-label" for="select-all-products">Pilih semua hasil filter ({{ $allProductIds->count() }})</label>
                        </div>
                        <span class="badge badge-warning" id="selected-products-count">0 produk dipilih</span>
                    </div>
                    <button type="submit" class="btn btn-danger" id="bulk-delete-button" disabled onclick="return confirmBulkDelete()">
                        <x-heroicon-o-trash class="w-5 h-5 mr-1" /> Tandai Hapus Terpilih
                    </button>
                </div>

                <div id="bulk-selected-inputs"></div>

                <div class="table-responsive rounded mb-3">
                    <table class="table mb-0">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">
                                <th><span class="sr-only">Pilih</span></th>
                                <th>No.</th>
                                <th>Foto</th>
                                <th><x-sort-link name="name" label="Nama" /></th>
                                <th><x-sort-link name="category.name" label="Kategori" /></th>
                                <th><x-sort-link name="selling_price" label="Harga" /></th>
                                <th><x-sort-link name="stock" label="Stock" /></th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="ligth-body">
                            @forelse ($products as $product)
                            <tr data-product-row="{{ $product->id }}">
                                <td>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input product-checkbox" id="product-checkbox-{{ $product->id }}" value="{{ $product->id }}">
                                        <label class="custom-control-label" for="product-checkbox-{{ $product->id }}"></label>
                                    </div>
                                </td>
                                <td>{{ (($products->currentPage() * request('row', 10)) - request('row', 10)) + $loop->iteration  }}</td>
                                <td>
                                    <img class="avatar-60 rounded" src="{{ $product->image ? asset('storage/products/'.$product->image) : asset('assets/images/product/default.webp') }}">
                                </td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category->name }}</td>
                                <td>{{ $product->selling_price }}</td>
                                <td>{{ $product->stock }}</td>
                                <td>
                                    @if ($product->expire_date > Carbon\Carbon::now()->format('Y-m-d'))
                                        <span class="badge rounded-pill bg-success">Valid</span>
                                    @else
                                        <span class="badge rounded-pill bg-danger">Invalid</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center list-action">
                                        <a class="btn btn-info mr-2" data-toggle="tooltip" data-placement="top" title="Lihat" href="{{ route('products.show', $product->id) }}">
                                            <x-heroicon-o-eye class="w-5 h-5 mr-0" />
                                        </a>
                                        <a class="btn btn-warning mr-2" data-toggle="tooltip" data-placement="top" title="Ubah" href="{{ route('products.edit', $product->id) }}">
                                            <x-heroicon-o-pencil class="w-5 h-5 mr-0" />
                                        </a>
                                        <button type="submit" form="delete-product-{{ $product->id }}" class="btn btn-danger border-0" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')" data-toggle="tooltip" data-placement="top" title="Hapus">
                                            <x-heroicon-o-trash class="w-5 h-5 mr-0" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">
                                    <span class="text-muted">Tidak ada produk ditemukan.</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            @foreach ($products as $product)
                <form id="delete-product-{{ $product->id }}" action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-none">
                    @method('delete')
                    @csrf
                </form>
            @endforeach

            <div class="d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>

<script>
    const allProductIds = @json($allProductIds);
    const selectedProductIds = new Set();
    const pageCheckbox = document.getElementById('select-page-products');
    const allCheckbox = document.getElementById('select-all-products');
    const countBadge = document.getElementById('selected-products-count');
    const bulkButton = document.getElementById('bulk-delete-button');
    const selectedInputs = document.getElementById('bulk-selected-inputs');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');

    function syncBulkSelection() {
        productCheckboxes.forEach((checkbox) => {
            const productId = Number(checkbox.value);
            const checked = selectedProductIds.has(productId);
            const row = document.querySelector(`[data-product-row="${productId}"]`);

            checkbox.checked = checked;
            if (row) {
                row.classList.toggle('product-row-selected', checked);
            }
        });

        pageCheckbox.checked = productCheckboxes.length > 0 && Array.from(productCheckboxes).every((checkbox) => checkbox.checked);
        allCheckbox.checked = allProductIds.length > 0 && selectedProductIds.size === allProductIds.length;
        countBadge.textContent = `${selectedProductIds.size} produk dipilih`;
        bulkButton.disabled = selectedProductIds.size === 0;
        selectedInputs.innerHTML = '';

        selectedProductIds.forEach((productId) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'product_ids[]';
            input.value = productId;
            selectedInputs.appendChild(input);
        });
    }

    productCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            const productId = Number(checkbox.value);

            if (checkbox.checked) {
                selectedProductIds.add(productId);
            } else {
                selectedProductIds.delete(productId);
            }

            syncBulkSelection();
        });
    });

    pageCheckbox.addEventListener('change', () => {
        productCheckboxes.forEach((checkbox) => {
            const productId = Number(checkbox.value);

            if (pageCheckbox.checked) {
                selectedProductIds.add(productId);
            } else {
                selectedProductIds.delete(productId);
            }
        });

        syncBulkSelection();
    });

    allCheckbox.addEventListener('change', () => {
        selectedProductIds.clear();

        if (allCheckbox.checked) {
            allProductIds.forEach((productId) => selectedProductIds.add(Number(productId)));
        }

        syncBulkSelection();
    });

    function confirmBulkDelete() {
        return selectedProductIds.size > 0 && confirm(`Tandai hapus ${selectedProductIds.size} produk terpilih?`);
    }

    syncBulkSelection();
</script>
@endsection
