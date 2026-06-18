@extends('dashboard.body.main')

@section('container')
    <style>
        .row-selector-container {
            min-width: 0; /* Default for mobile - allows shrinking */
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
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

                <!-- Alert: Success Message -->
                @if (session()->has('success'))
                    <div class="alert text-white bg-success" role="alert">
                        <div class="iq-alert-text">{{ session('success') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                @endif

                <!-- Alert: Error Message -->
                @if (session()->has('error'))
                    <div class="alert text-white bg-danger" role="alert">
                        <div class="iq-alert-text">{{ session('error') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                @endif

                <!-- Header: Page Title and Clear Search -->
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="mb-3">Daftar Order Tertunda</h4>
                        <p class="mb-0">Order yang sedang tertunda. Anda bisa melihat detail untuk menyelesaikannya.</p>
                    </div>
                    <div>
                        <a href="{{ route('order.pendingOrders') }}" class="btn btn-danger add-list d-flex align-items-center">
                            <x-heroicon-o-trash class="w-5 h-5 mr-1" /> Bersihkan Pencarian
                        </a>
                    </div>
                </div>
                </div>

            <div class="col-lg-12">
                <!-- Main Card -->
                <div class="card">
                    <div class="card-body">

                        <!-- Filter Form -->
                        <form action="{{ route('order.pendingOrders') }}" method="get">
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                <!-- Row Selector -->
                                <div class="form-group mb-0 mr-2 mt-n3 row-selector-container">
                                    <div class="d-flex align-items-center">
                                        <label for="row" class="mb-0 mr-2" style="min-width: 50px;">Baris:</label>
                                        <select class="form-control" name="row">
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

                                <!-- Search Input -->
                                <div class="form-group row">
                                    <label class="control-label col-sm-3 align-self-center" for="search">Cari:</label>
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            <input type="text" id="search" class="form-control" name="search" placeholder="Cari order"
                                                value="{{ request('search') }}">
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

                        <!-- Orders Table -->
                        <div class="table-responsive rounded mb-3">
                            <table class="table mb-0">
                                <thead class="bg-white text-uppercase">
                                    <tr class="ligth ligth-data">
                                        <th>No.</th>
                                        <th>No. Faktur</th>
                                        <th><x-sort-link name="customer.name" label="Nama" /></th>
                                        <th><x-sort-link name="order_date" label="Tanggal Order" /></th>
                                        <th>Pembayaran</th>
                                        <th><x-sort-link name="total" label="Total" /></th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="ligth-body">
                                    @forelse ($orders as $order)
                                        <tr>
                                            <td>{{ (($orders->currentPage() * 10) - 10) + $loop->iteration }}</td>
                                            <td>{{ $order->invoice_no }}</td>
                                            <td>{{ $order->customer->name }}</td>
                                            <td>{{ $order->order_date->format('Y-m-d') }}</td>
                                            <td>{{ $order->payment_type }}</td>
                                            <td>{{ number_format($order->total, 2) }}</td>
                                            <td>
                                                @if($order->order_status == 'pending')
                                                    <span class="badge badge-warning">Tertunda</span>
                                                @elseif($order->order_status == 'cancelled')
                                                    <span class="badge badge-danger">Dibatalkan</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center list-action">
                                                    <a class="btn btn-info mr-2" data-toggle="tooltip" data-placement="top" title="Detail"
                                                        href="{{ route('order.orderDetails', $order->id) }}">
                                                        <x-heroicon-o-eye class="w-5 h-5 mr-0" />
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">Tidak ada order tertunda ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        {{ $orders->links() }}
                        </div>
                        </div>
                        </div>
                        </div>
                        </div>
@endsection
