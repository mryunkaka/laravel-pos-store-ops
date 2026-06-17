@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">

                {{-- Alert: Error --}}
                @if (session()->has('error'))
                    <div class="alert text-white bg-danger" role="alert">
                        <div class="iq-alert-text">{{ session('error') }}</div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Order Details Information
                                @if($order->order_status == 'cancelled')
                                    <span class="badge badge-danger ml-2">Cancelled</span>
                                @elseif($order->order_status == 'void')
                                    <span class="badge badge-dark ml-2">Voided</span>
                                @endif
                            </h4>
                        </div>
                        <div>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                                <x-heroicon-o-arrow-left class="w-4 h-4 mr-1 inline" /> Back
                            </a>
                        </div>
                        </div>

                    <div class="card-body">
                        <!-- Customer Profile Info -->
                        <div class="d-flex align-items-center mb-4">
                            <div>
                                <h5 class="mb-1">{{ $order->customer->name }}</h5>
                                <p class="mb-0 text-muted">{{ $order->customer->email }}</p>
                                <p class="mb-0 text-muted">{{ $order->customer->address }}</p>
                            </div>
                        </div>

                        <!-- Order Information Form (Read Only) -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Customer Name</label>
                                    <input type="text" class="form-control bg-white" value="{{ $order->customer->name }}" readonly>
                                    </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Customer Phone</label>
                                    <input type="text" class="form-control bg-white" value="{{ $order->customer->phone }}" readonly>
                                    </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Order Date</label>
                                    <input type="text" class="form-control bg-white" value="{{ $order->order_date->format('Y-m-d') }}" readonly>
                                    </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Order Invoice</label>
                                            <input class="form-control bg-white" value="{{ $order->invoice_no }}" readonly />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Payment Type</label>
                                            <input class="form-control bg-white" value="{{ $order->payment_type }}" readonly />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Paid Amount</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                        <input type="text" class="form-control bg-white" value="{{ number_format($order->pay_amount, 2) }}" readonly>
                                        </div>
                                        </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Due Amount</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">$</span>
                                                    </div>
                                        <input type="text" class="form-control bg-white" value="{{ number_format($order->due_amount, 2) }}" readonly>
                                        </div>
                                        </div>
                                        </div>
                                        </div>

                        <!-- Actions for Pending Orders -->
                        @if ($order->order_status == 'pending')
                            <div class="row mt-4">
                                <div class="col-lg-12 d-flex justify-content-end">
                                    <form action="{{ route('order.updateStatus') }}" method="POST" class="d-inline">
                                        @method('put')
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $order->id }}">

                                        <button type="button" class="btn btn-outline-danger mr-2" data-toggle="modal" data-target="#cancelModal">
                                            <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel Order
                                        </button>

                                        <button type="submit" class="btn btn-success"
                                            onclick="return confirm('Are you sure you want to complete this order? This reduces stock.')">
                                            <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Complete Order
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Cancel Modal -->
                            <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <form action="{{ route('order.cancel') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="cancelModalLabel">Cancel Order {{ $order->invoice_no }}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Reason for cancellation <span class="text-danger">*</span></label>
                                                    <textarea name="cancel_reason" class="form-control" rows="3" required placeholder="Enter reason for cancelling this order..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-danger">Confirm Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @elseif ($order->order_status == 'complete')
                            <div class="row mt-3">
                                <div class="col-lg-12">
                                    <div class="alert alert-success text-center" role="alert">
                                        <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> This order is completed.
                                    </div>
                                </div>
                            </div>
                            @can('void.order')
                                <div class="row mt-2">
                                    <div class="col-lg-12 d-flex justify-content-end">
                                        <button type="button" class="btn btn-outline-dark" data-toggle="modal" data-target="#voidModal">
                                            <x-heroicon-o-x-circle class="w-5 h-5 mr-1 inline" /> Void Order
                                        </button>
                                    </div>
                                </div>

                                <!-- Void Modal -->
                                <div class="modal fade" id="voidModal" tabindex="-1" role="dialog" aria-labelledby="voidModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <form action="{{ route('order.void') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="voidModalLabel">Void Order {{ $order->invoice_no }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-warning">
                                                        <strong>Warning:</strong> Voiding this order will restore all product stock. This action cannot be undone.
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Reason for void <span class="text-danger">*</span></label>
                                                        <textarea name="void_reason" class="form-control" rows="3" required placeholder="Enter reason for voiding this order..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-dark">Confirm Void</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endcan
                        @elseif ($order->order_status == 'cancelled')
                            <div class="row mt-3">
                                <div class="col-lg-12">
                                    <div class="alert alert-danger" role="alert">
                                        <x-heroicon-o-x-circle class="w-5 h-5 mr-1 inline" /> <strong>Order Cancelled</strong><br>
                                        Reason: {{ $order->cancel_reason }}<br>
                                        <small>Cancelled by: {{ $order->cancelledBy->name ?? 'N/A' }} at {{ $order->cancelled_at?->format('Y-m-d H:i') ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>
                        @elseif ($order->order_status == 'void')
                            <div class="row mt-3">
                                <div class="col-lg-12">
                                    <div class="alert alert-dark" role="alert">
                                        <x-heroicon-o-x-circle class="w-5 h-5 mr-1 inline" /> <strong>Order Voided</strong><br>
                                        Reason: {{ $order->void_reason }}<br>
                                        <small>Voided by: {{ $order->voidedBy->name ?? 'N/A' }} at {{ $order->voided_at?->format('Y-m-d H:i') ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                        </div>
                        </div>
                        </div>

            <!-- Order Items Table -->
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Order Items</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive rounded">
                            <table class="table mb-0">
                                <thead class="bg-light text-uppercase">
                                    <tr class="ligth ligth-data">
                                        <th>No.</th>
                                        <th>Photo</th>
                                        <th>Product Name</th>
                                        <th>Product Code</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody class="ligth-body">
                                    @foreach ($orderDetails as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <img class="avatar-50 rounded"
                                                    src="{{ $item->product->image ? asset('storage/products/' . $item->product->image) : asset('assets/images/product/default.webp') }}"
                                                    alt="{{ $item->product->name }}" style="object-fit: cover;">
                                            </td>
                                            <td>{{ $item->product->name }}</td>
                                            <td>{{ $item->product->code }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->unit_price, 2) }}</td>
                                            <td>{{ number_format($item->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="6" class="text-right font-weight-bold">Subtotal</td>
                                        <td class="font-weight-bold">{{ number_format($order->sub_total, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right font-weight-bold">VAT</td>
                                        <td class="font-weight-bold">{{ number_format($order->vat, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right font-weight-bold text-primary" style="font-size: 1.1em;">
                                            Total</td>
                                        <td class="font-weight-bold text-primary" style="font-size: 1.1em;">
                                            {{ number_format($order->total, 2) }}
                                        </td>
                                        </tr>
                                        </tfoot>
                                        </table>
                                        </div>
                                        </div>
                                        </div>
                                        </div>
        </div>
        </div>
@endsection
