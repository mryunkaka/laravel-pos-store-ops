@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <!-- Success Alert -->
        <div class="row">
            <div class="col-lg-12">
                @if (session()->has('success'))
                    <div class="alert text-white bg-success" role="alert">
                        <div class="iq-alert-text">{{ session('success') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert text-white bg-danger" role="alert">
                        <div class="iq-alert-text">{{ session('error') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <!-- LEFT COLUMN: Product Catalog -->
            <div class="col-md-12 col-lg-8">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card card-block card-stretch card-height">
                            <div class="card-body">
                                <!-- Filter & Search Form -->
                                <form action="{{ route('pos.index') }}" method="get">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                                        <!-- Search Input -->
                                        <div class="form-group row mb-0 col-md-5">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="search" id="pos_search"
                                                    placeholder="Cari nama atau barcode..." value="{{ request('search') }}" autocomplete="off">
                                                <div class="input-group-append">
                                                    <button type="submit" class="input-group-text bg-primary text-white">
                                                        <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                                                    </button>
                                                    @if (request('search') || request('category_id'))
                                                        <a href="{{ route('pos.index') }}" class="input-group-text bg-danger text-white">
                                                                <x-heroicon-o-x-mark class="w-5 h-5" />
                                                            </a>
                                                    @endif
                                                </div>
                                                </div>
                                        </div>

                                        <!-- Category Filter -->
                                        <div class="form-group row mb-0 col-md-4">
                                            <select class="form-control" name="category_id" onchange="this.form.submit()">
                                                <option value="">Semua Kategori</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                            {{ $category->name }}
                                                        </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Pagination Limit -->
                                        <div class="form-group row mb-0 col-md-3">
                                            <select class="form-control" name="row" onchange="this.form.submit()">
                                                <option value="10" {{ request('row') == '10' ? 'selected' : '' }}>10 /
                                                    Halaman</option>
                                                <option value="20" {{ request('row') == '20' ? 'selected' : '' }}>20 /
                                                    Halaman</option>
                                                <option value="50" {{ request('row') == '50' ? 'selected' : '' }}>50 /
                                                    Halaman</option>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Product Grid -->
                    <div class="col-lg-12">
                        <div class="row">
                            @forelse($products as $product)
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                    <div class="product-card h-100 d-flex flex-column">
                                        <!-- Product Image -->
                                        <div class="image-container">
                                            <img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('assets/images/product/default.webp') }}"
                                                class="product-image" alt="{{ $product->name }}">

                                            <!-- Stock Badge -->
                                            <span class="badge position-absolute shadow-sm"
                                                style="top: 12px; right: 12px; font-size: 0.75rem; padding: 0.5em 0.8em; {{ $product->stock > 10 ? 'background-color: #10b981; color: white;' : 'background-color: #ef4444; color: white;' }}">
                                                Stok: {{ $product->stock }}
                                            </span>
                                        </div>

                                        <!-- Product Content -->
                                        <div class="p-3 d-flex flex-column flex-grow-1">
                                            <h6 class="font-weight-bold text-dark text-truncate mb-2"
                                                title="{{ $product->name }}" style="font-size: 0.95rem;">
                                                {{ $product->name }}
                                            </h6>

                                            <div class="d-flex align-items-center justify-content-between mt-auto">
                                                <h5 class="text-primary font-weight-bolder mb-0" style="font-size: 1.1rem;">
                                                    {{ number_format($product->selling_price) }}
                                                </h5>

                                                <!-- Add to Cart Form -->
                                                <form class="add-to-cart-form" onsubmit="addToCart(event)">
                                                    <input type="hidden" name="id" value="{{ $product->id }}">
                                                    <input type="hidden" name="name" value="{{ $product->name }}">
                                                    <input type="hidden" name="price" value="{{ $product->selling_price }}">
                                                    <button type="submit"
                                                        class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center">
                                                        <x-heroicon-o-plus class="w-4 h-4 mr-1" /> Tambah
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-info text-center">
                                        <x-heroicon-o-information-circle class="w-6 h-6 mx-auto mb-2" />
                                        Produk tidak ditemukan.
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <!-- Pagination Links -->
                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-center">
                                {{ $products->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Cart System -->
            <div class="col-md-12 col-lg-4">
                <div class="card border-0 shadow-lg sticky-top" style="top: 20px; z-index: 100;">
                    <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between p-3">
                        <h5 class="mb-0 text-white">
                            <x-heroicon-o-shopping-cart class="w-5 h-5 mr-1 inline" /> Order Saat Ini
                        </h5>
                        <span class="badge badge-light text-primary font-weight-bold" id="cart-count-badge">
                            {{ Cart::count() }} item
                        </span>
                    </div>

                    <div class="card-body p-0">
                        <!-- Customer Selection -->
                        <div class="p-3 border-bottom bg-light">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold mb-1">Pelanggan</label>
                                <div class="input-group">
                                    <select class="form-control select2" id="customer_id" name="customer_id" style="width: 85%;">
                                        <option value="" selected disabled>-- Cari Pelanggan --</option>
                                    </select>
                                    <div class="input-group-append" style="width: 15%;">
                                        <button type="button" class="btn btn-outline-primary btn-block" title="Tambah Pelanggan Baru" data-toggle="modal"
                                            data-target="#addCustomerModal">
                                            <x-heroicon-o-plus class="w-5 h-5 mx-auto" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Cart Sidebar -->
                        <div id="cart-sidebar-container">
                            @include('pos.cart-sidebar')
                        </div>
                    </div>
                    </div>
                    </div>
        </div>
    </div>

    <!-- Payment Confirmation Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header bg-primary text-white"
                    style="border-top-left-radius: 20px; border-top-right-radius: 20px;">
                    <h5 class="modal-title font-weight-bold mx-auto">Konfirmasi Pembayaran</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form onsubmit="submitOrder(event)">
                    @csrf
                    <div class="modal-body p-4">
                        <input type="hidden" id="modal_customer_id" name="customer_id" required>

                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted font-weight-bold">Total Tagihan:</td>
                                    <td class="text-right font-weight-bold h5 text-primary" id="modal_total_display">
                                        {{ Cart::total() }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted font-weight-bold">Dibayar Dengan:</td>
                                    <td class="text-right font-weight-bold" id="modal_payment_method">Tunai</td>
                                </tr>
                                <tr>
                                    <td class="text-muted font-weight-bold">Jumlah Dibayar:</td>
                                    <td class="text-right font-weight-bold h5 text-success" id="modal_pay_amount">0.00
                                    </td>
                                </tr>
                                <tr class="border-top">
                                    <td class="text-muted font-weight-bold">Kembalian:</td>
                                    <td class="text-right font-weight-bold h5 text-danger" id="modal_change_amount">0.00
                                    </td>
                                </tr>
                            </table>
                            </div>
                            </div>
                            <!-- Modal Actions -->
                            <div class="modal-footer border-top-0 d-flex justify-content-between p-4 bg-light"
                                style="border-bottom-left-radius: 20px; border-bottom-right-radius: 20px;">
                                <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary px-5 shadow-sm">Konfirmasi Pembayaran</button>
                            </div>
                            </form>
                            </div>
                            </div>
                            </div>

    <!-- Create Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold">Tambah Pelanggan Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <!-- AJAX Form Submission -->
                <form onsubmit="storeCustomer(event)">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="name">Nama <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="phone">Telepon</label>
                                <input type="text" class="form-control" name="phone">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="city">Kota</label>
                                <input type="text" class="form-control" name="city">
                            </div>
                            <div class="col-md-12 form-group">
                                <label for="address">Alamat</label>
                                <textarea class="form-control" name="address" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan Pelanggan</button>
                    </div>
                </form>
            </div>
        </div>
        </div>
@endsection

@section('specificpagescripts')
    <!-- External Dependencies: Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Initialize Select2 on Load
            window.addEventListener('load', function () {
            $('.select2').select2({
                placeholder: "-- Cari Pelanggan --",
                allowClear: true,
                width: 'resolve',
                ajax: {
                    url: "{{ route('pos.customers.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            term: params.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                }
            });
        });

        // Helper: Get Selected Customer ID
        function getCustomerId() {
            return $('#customer_id').val();
        }

        // Logic: Add Item to Cart (AJAX)
        async function addToCart(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const customerId = getCustomerId();

            if (customerId) formData.append('customer_id', customerId);

            try {
                const response = await fetch("{{ route('pos.addCart') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    // Update Cart Sidebar HTML
                    document.getElementById('cart-sidebar-container').innerHTML = data.cart_html;
                    // Update Cart Count Badge
                    document.getElementById('cart-count-badge').innerText = data.cart_count + ' item';
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
            }
        }

        // Logic: Update Item Quantity (AJAX)
        async function updateCart(rowId, qty) {
            const customerId = getCustomerId();
            try {
                const response = await fetch("{{ url('pos/update') }}/" + rowId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        qty: qty,
                        customer_id: customerId
                    })
                });
                const data = await response.json();
                if (data.success) {
                    document.getElementById('cart-sidebar-container').innerHTML = data.cart_html;
                    document.getElementById('cart-count-badge').innerText = data.cart_count + ' item';
                }
            } catch (error) {
                console.error('Error updating cart:', error);
            }
        }

        // Logic: Remove Item from Cart (AJAX)
        async function deleteCart(rowId) {
            const customerId = getCustomerId();
            try {
                const response = await fetch("{{ url('pos/delete') }}/" + rowId + "?customer_id=" + (customerId || ''), {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    document.getElementById('cart-sidebar-container').innerHTML = data.cart_html;
                    document.getElementById('cart-count-badge').innerText = data.cart_count + ' item';
                }
            } catch (error) {
                console.error('Error deleting cart:', error);
            }
        }

        // Logic: Create New Customer (AJAX)
        async function storeCustomer(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            try {
                const response = await fetch("{{ route('pos.storeCustomer') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    // Append new customer to dropdown and select it
                    var text = data.customer.name + ' (' + (data.customer.phone || 'N/A') + ')';
                    var newOption = new Option(text, data.customer.id, true, true);
                    $('#customer_id').append(newOption).trigger('change');

                    // Close modal and reset form
                    $('#addCustomerModal').modal('hide');
                    form.reset();
                    alert(data.message);
                } else {
                    alert('Gagal membuat pelanggan');
                }
            } catch (error) {
                console.error('Error creating customer:', error);
                alert('Gagal membuat pelanggan. Periksa input.');
            }
        }

        // Logic: Real-time Change Calculation
        function getPaymentRows() {
            return Array.from(document.querySelectorAll('#payments-container .payment-row')).map(function(row) {
                const typeElement = row.querySelector('.payment-type');
                const amountElement = row.querySelector('.payment-amount');

                return {
                    payment_type: typeElement ? typeElement.value : 'cash',
                    amount: parseFloat(amountElement && amountElement.value ? amountElement.value : 0)
                };
            }).filter(function(payment) {
                return payment.amount > 0;
            });
        }

        function getPaymentTotal() {
            return getPaymentRows().reduce(function(total, payment) {
                return total + payment.amount;
            }, 0);
        }

        function parseNumberText(value) {
            return parseFloat(String(value || '0').replace(/,/g, '').replace(/\./g, '')) || 0;
        }

        function getOrderTotalAmount() {
            const totalElement = document.getElementById('cart-total');
            const baseTotal = totalElement ? parseFloat(totalElement.dataset.baseTotal || 0) : 0;
            const invoiceDiscount = parseFloat(document.getElementById('invoice_discount')?.value || 0);
            const serviceCharge = parseFloat(document.getElementById('service_charge')?.value || 0);
            return Math.max(baseTotal - invoiceDiscount + serviceCharge, 0);
        }

        function refreshOrderTotalDisplay() {
            const totalElement = document.getElementById('cart-total');
            if (!totalElement) return;
            totalElement.innerText = getOrderTotalAmount().toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function getPaymentSummary() {
            const labels = {
                cash: 'Tunai',
                qris: 'QRIS',
                debit: 'Debit',
                transfer: 'Transfer',
                ewallet: 'E-Wallet'
            };

            return getPaymentRows().map(function(payment) {
                return (labels[payment.payment_type] || payment.payment_type) + ' ' + payment.amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }).join(', ');
        }

        function addPaymentRow() {
            const container = document.getElementById('payments-container');
            if (!container) return;

            const row = document.createElement('div');
            row.className = 'row payment-row';
            row.innerHTML = `
                <div class="col-5 pr-1">
                    <div class="form-group mb-2">
                        <select class="form-control form-control-sm payment-type" onchange="calculateChange()">
                            <option value="cash">Tunai</option>
                            <option value="qris">QRIS</option>
                            <option value="debit">Debit</option>
                            <option value="transfer">Transfer</option>
                            <option value="ewallet">E-Wallet</option>
                        </select>
                    </div>
                </div>
                <div class="col-5 px-1">
                    <div class="form-group mb-2">
                        <input type="number" class="form-control form-control-sm payment-amount" placeholder="0" oninput="calculateChange()" min="0">
                    </div>
                </div>
                <div class="col-2 pl-1">
                    <button type="button" class="btn btn-outline-danger btn-sm btn-block" onclick="removePaymentRow(this)" title="Hapus pembayaran">&times;</button>
                </div>
            `;
            container.appendChild(row);
        }

        function removePaymentRow(button) {
            button.closest('.payment-row').remove();
            calculateChange();
        }

        function calculateChange() {
            refreshOrderTotalDisplay();
            const totalAmount = getOrderTotalAmount();
            const payInput = getPaymentTotal();
            const changeElement = document.getElementById('change_amount');

            if (!isNaN(payInput) && payInput >= 0) {
                const change = payInput - totalAmount;
                changeElement.innerText = change.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");

                if (change < 0) {
                    changeElement.classList.add('text-danger');
                    changeElement.classList.remove('text-success');
                } else {
                    changeElement.classList.remove('text-danger');
                    changeElement.classList.add('text-success');
                }
            } else {
                changeElement.innerText = "0.00";
                changeElement.classList.remove('text-success', 'text-danger');
            }
        }

        // Logic: Validate Payment & Show Summary Modal
        function validateAndShowModal() {
            // 1. Ensure Customer Selected
            var customerId = $('#customer_id').val();
            if (!customerId) {
                alert("Silakan pilih pelanggan terlebih dahulu!");
                return;
            }
            document.getElementById('modal_customer_id').value = customerId;

            // 2. Validate Payment Amount
            refreshOrderTotalDisplay();
            const totalText = document.getElementById('cart-total').innerText;
            const totalAmount = getOrderTotalAmount();
            const payAmount = getPaymentTotal();
            const method = getPaymentSummary();

            if (isNaN(payAmount) || payAmount <= 0) {
                alert('Masukkan jumlah yang valid!');
                return;
            }

            if (payAmount < totalAmount) {
                alert('Pembayaran kurang! Total tagihan ' + totalText);
                return;
            }

            // 3. Update Modal UI
            document.getElementById('modal_total_display').innerText = totalText;
            document.getElementById('modal_payment_method').innerText = method;
            document.getElementById('modal_pay_amount').innerText = payAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,
                ",");

            const change = payAmount - totalAmount;
            document.getElementById('modal_change_amount').innerText = change.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,
                ",");

            // 4. Show Modal
            $('#paymentModal').modal('show');
        }

        // Logic: Final Order Submission (AJAX)
        async function submitOrder(event) {
            event.preventDefault();

            // Construct FormData manually since inputs are in Sidebar, not in this Form
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('customer_id', document.getElementById('modal_customer_id').value);

            const paymentTypeElem = document.getElementById('payment_type');
            const payAmountElem = document.getElementById('pay_amount');
            const payments = getPaymentRows();

            if (paymentTypeElem) formData.append('payment_type', paymentTypeElem.value);
            formData.append('pay_amount', getPaymentTotal());
            formData.append('invoice_discount', document.getElementById('invoice_discount')?.value || 0);
            formData.append('service_charge', document.getElementById('service_charge')?.value || 0);
            formData.append('voucher_code', document.getElementById('voucher_code')?.value || '');
            payments.forEach(function(payment, index) {
                formData.append(`payments[${index}][payment_type]`, payment.payment_type);
                formData.append(`payments[${index}][amount]`, payment.amount);
            });

            try {
                const response = await fetch("{{ route('pos.storeOrder') }}", {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    $('#paymentModal').modal('hide');

                    // Allow popup for invoice
                    if (data.invoice_url) {
                        window.open(data.invoice_url, '_blank');
                    }

                    // Reset UI
                    if (data.cart_html) {
                        document.getElementById('cart-sidebar-container').innerHTML = data.cart_html;
                    }

                    // Update Cart Count Badge
                    if (data.cart_count !== undefined) {
                        document.getElementById('cart-count-badge').innerText = data.cart_count + ' item';
                    }

                    // Optional: Reset Pay Input
                    document.querySelectorAll('.payment-amount').forEach(function(input) {
                        input.value = '';
                    });
                    if (document.getElementById('change_amount')) document.getElementById('change_amount').innerText =
                        '0.00';

                    alert('Order Berhasil!');

                } else {
                    alert('Order Gagal: ' + (data.message || 'Kesalahan tidak diketahui'));
                }
            } catch (error) {
                console.error('Error submitting order:', error);
                alert('Terjadi kesalahan saat memproses order.');
            }
        }

        async function quickAddBarcode(code) {
            try {
                const response = await fetch("{{ route('pos.barcode.quickAdd') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ code: code })
                });
                const data = await response.json();
                if (!data.success) {
                    alert(data.message || 'Produk tidak ditemukan');
                    return;
                }

                const formData = new FormData();
                formData.append('id', data.product.id);
                formData.append('name', data.product.name);
                formData.append('price', data.product.price);

                const addResponse = await fetch("{{ route('pos.addCart') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const addData = await addResponse.json();
                if (addData.success) {
                    document.getElementById('cart-sidebar-container').innerHTML = addData.cart_html;
                    document.getElementById('cart-count-badge').innerText = addData.cart_count + ' item';
                } else {
                    alert(addData.message || 'Gagal menambah produk');
                }
            } catch (error) {
                console.error('Barcode quick add error:', error);
                alert('Gagal memproses barcode.');
            }
        }

        // Barcode Scanner Handling for POS Search
        (function() {
            const posSearchField = document.getElementById('pos_search');
            const searchForm = posSearchField ? posSearchField.closest('form') : null;
            
            if (posSearchField && searchForm) {
                let scannerTimeout;
                
                // Auto-focus search field on mobile devices
                function isMobileDevice() {
                    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
                           (window.innerWidth <= 768);
                }
                
                // Handle scanner input (scanners typically send Enter after barcode)
                posSearchField.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.keyCode === 13) {
                        // Clear any existing timeout
                        if (scannerTimeout) {
                            clearTimeout(scannerTimeout);
                        }
                        
                        const searchValue = posSearchField.value.trim();
                        if (searchValue) {
                            scannerTimeout = setTimeout(function() {
                                quickAddBarcode(searchValue);
                                posSearchField.value = '';
                            }, 100);
                        }
                    }
                });
                
                // Handle paste events (some scanners use paste)
                posSearchField.addEventListener('paste', function(e) {
                    setTimeout(function() {
                        const pastedValue = posSearchField.value.trim();
                        if (pastedValue) {
                            quickAddBarcode(pastedValue);
                            posSearchField.value = '';
                        }
                    }, 50);
                });
                
                // Auto-focus on mobile for better scanner experience
                if (isMobileDevice()) {
                    setTimeout(function() {
                        posSearchField.focus();
                    }, 500);
                }
            }
        })();
    </script>

    <!-- Page Specific Styles -->
    <style>
        /* Modern Scrollbar for Cart */
        .cart-items-wrapper::-webkit-scrollbar {
            width: 5px;
        }

        .cart-items-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .cart-items-wrapper::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }

        .cart-items-wrapper::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* Product Card Hover Effects */
        .product-card {
            border: 1px solid #f3f4f6;
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fff;
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: #e5e7eb;
        }

        .product-image {
            height: 180px;
            width: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .image-container {
            overflow: hidden;
            position: relative;
        }

        /* Circular Buttons */
        .btn-circle {
            width: 28px;
            height: 28px;
            padding: 0;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .btn-circle:hover {
            background-color: #f3f4f6;
        }

        /* Select2 Styling Overrides */
        .select2-container--default .select2-selection--single {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            height: 45px;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 45px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-left: 15px;
            font-size: 0.95rem;
            color: #374151;
        }
    </style>
@endsection
