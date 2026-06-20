@extends('dashboard.body.main')

@section('specificpagestyles')
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://unpkg.com/gijgo@1.9.14/js/gijgo.min.js" type="text/javascript"></script>
    <link href="https://unpkg.com/gijgo@1.9.14/css/gijgo.min.css" rel="stylesheet" type="text/css" />
@endsection

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Tambah Produk</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <!-- begin: Input Image -->
                            <div class="form-group row align-items-center">
                                <div class="col-md-12">
                                    <div class="profile-img-edit">
                                        <div class="crm-profile-img-edit">
                                            <img class="crm-profile-pic rounded-circle avatar-100" id="image-preview"
                                                src="{{ asset('assets/images/product/default.webp') }}" alt="profile-pic">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="input-group mb-4 col-lg-6">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input @error('image') is-invalid @enderror" id="image" name="image"
                                            accept="image/*" onchange="previewImage();">
                                        <label class="custom-file-label" for="image">Choose file</label>
                                        </div>
                                    @error('image')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    </div>
                                    </div>
                                    <!-- end: Input Image -->

                            <!-- begin: Input Data -->
                            <div class="row align-items-center">
                                <div class="form-group col-md-12">
                                    <label for="name">Nama Produk <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                                        value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    </div>

                                <div class="form-group col-md-6">
                                    <label for="code">Kode Produk</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code"
                                        value="{{ old('code') }}" placeholder="Kosongkan untuk generate otomatis" maxlength="50">
                                    <small class="form-text text-muted">Opsional: Kosongkan untuk generate otomatis atau scan barcode di bawah</small>
                                    @error('code')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="barcode_scanner">Barcode Scanner</label>
                                    <input type="text" class="form-control" id="barcode_scanner" 
                                        placeholder="Scan barcode di sini (mobile-friendly)" autocomplete="off">
                                    <small class="form-text text-muted">Scan barcode untuk mengisi Kode Produk otomatis</small>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="category_id">Kategori <span class="text-danger">*</span></label>
                                    <select class="form-control" name="category_id" required>
                                        <option selected="" disabled>-- Pilih Kategori --</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="stock">Stok</label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock"
                                        value="{{ old('stock') }}">
                                    @error('stock')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    </div>

                                <div class="form-group col-md-6">
                                    <label for="buying_price">Harga Beli <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('buying_price') is-invalid @enderror" id="buying_price"
                                        name="buying_price" value="{{ old('buying_price') }}" required>
                                    @error('buying_price')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    </div>

                                <div class="form-group col-md-6">
                                    <label for="selling_price">Harga Jual <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('selling_price') is-invalid @enderror" id="selling_price"
                                        name="selling_price" value="{{ old('selling_price') }}" required>
                                    @error('selling_price')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    </div>

                                <div class="form-group col-md-3">
                                    <label for="discount">Diskon Item</label>
                                    <input type="number" class="form-control @error('discount') is-invalid @enderror" id="discount" name="discount" value="{{ old('discount', 0) }}" min="0" step="0.01">
                                    @error('discount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="discount_type">Tipe Diskon</label>
                                    <select class="form-control @error('discount_type') is-invalid @enderror" id="discount_type" name="discount_type">
                                        <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Nominal</option>
                                        <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Persen</option>
                                    </select>
                                    @error('discount_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="wholesale_price">Harga Grosir</label>
                                    <input type="number" class="form-control @error('wholesale_price') is-invalid @enderror" id="wholesale_price" name="wholesale_price" value="{{ old('wholesale_price') }}" min="0" step="0.01">
                                    @error('wholesale_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="wholesale_qty">Min. Qty Grosir</label>
                                    <input type="number" class="form-control @error('wholesale_qty') is-invalid @enderror" id="wholesale_qty" name="wholesale_qty" value="{{ old('wholesale_qty') }}" min="1">
                                    @error('wholesale_qty')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="tax_rate">Pajak Produk (%)</label>
                                    <input type="number" class="form-control @error('tax_rate') is-invalid @enderror" id="tax_rate" name="tax_rate" value="{{ old('tax_rate', 0) }}" min="0" max="100" step="0.01">
                                    @error('tax_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="buying_date">Tanggal Beli</label>
                                    <input id="buying_date" class="form-control @error('buying_date') is-invalid @enderror" name="buying_date"
                                        value="{{ old('buying_date') }}" />
                                    @error('buying_date')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="expire_date">Tanggal Kadaluarsa</label>
                                    <input id="expire_date" class="form-control @error('expire_date') is-invalid @enderror" name="expire_date"
                                        value="{{ old('expire_date') }}" />
                                    @error('expire_date')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <!-- end: Input Data -->

                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Save
                                </button>
                                <a class="btn btn-danger" href="{{ route('products.index') }}">
                                    <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel
                                </a>
                            </div>
                                </form>
                                </div>
                                </div>
                                </div>
                                </div>
                                <!-- Page end  -->
                                </div>

    <script>
        $('#buying_date').datepicker({
            uiLibrary: 'bootstrap4',
            format: 'yyyy-mm-dd'
            // https://gijgo.com/datetimepicker/configuration/format
        });
        $('#expire_date').datepicker({
            uiLibrary: 'bootstrap4',
            format: 'yyyy-mm-dd'
            // https://gijgo.com/datetimepicker/configuration/format
        });

        // Barcode Scanner Handling
        (function() {
            const barcodeScanner = document.getElementById('barcode_scanner');
            const codeField = document.getElementById('code');
            
            if (barcodeScanner && codeField) {
                let scannerTimeout;
                
                // Auto-focus scanner field on mobile devices
                function isMobileDevice() {
                    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
                           (window.innerWidth <= 768);
                }
                
                if (isMobileDevice()) {
                    // Auto-focus scanner field on mobile after a short delay
                    setTimeout(function() {
                        barcodeScanner.focus();
                    }, 300);
                }
                
                // Handle scanner input (scanners typically send Enter after barcode)
                barcodeScanner.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.keyCode === 13) {
                        e.preventDefault();
                        
                        const scannedValue = barcodeScanner.value.trim();
                        if (scannedValue) {
                            // Populate code field with scanned value
                            codeField.value = scannedValue;
                            
                            // Visual feedback
                            codeField.classList.add('border-success');
                            setTimeout(function() {
                                codeField.classList.remove('border-success');
                            }, 1000);
                            
                            // Clear scanner field
                            barcodeScanner.value = '';
                            
                            // Re-focus scanner for next scan
                            if (isMobileDevice()) {
                                setTimeout(function() {
                                    barcodeScanner.focus();
                                }, 100);
                            }
                        }
                    }
                });
                
                // Handle paste events (some scanners use paste)
                barcodeScanner.addEventListener('paste', function(e) {
                    setTimeout(function() {
                        const pastedValue = barcodeScanner.value.trim();
                        if (pastedValue) {
                            codeField.value = pastedValue;
                            codeField.classList.add('border-success');
                            setTimeout(function() {
                                codeField.classList.remove('border-success');
                            }, 1000);
                            barcodeScanner.value = '';
                        }
                    }, 10);
                });
            }
        })();
    </script>

    @include('components.preview-img-form')
@endsection
