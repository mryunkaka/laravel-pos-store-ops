@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
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

                <div id="invoice-copy-status" class="alert alert-info d-none" role="status"></div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Informasi Detail Order
                                @if($order->order_status == 'cancelled')
                                    <span class="badge badge-danger ml-2">Dibatalkan</span>
                                @elseif($order->order_status == 'void')
                                    <span class="badge badge-dark ml-2">Di-void</span>
                                @endif
                            </h4>
                        </div>
                        <div>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                                <x-heroicon-o-arrow-left class="w-4 h-4 mr-1 inline" /> Kembali
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
                                    <label>Nama Pelanggan</label>
                                    <input type="text" class="form-control bg-white" value="{{ $order->customer->name }}" readonly>
                                    </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Telepon Pelanggan</label>
                                    <input type="text" class="form-control bg-white" value="{{ $order->customer->phone }}" readonly>
                                    </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tanggal Order</label>
                                    <input type="text" class="form-control bg-white" value="{{ $order->order_date->format('Y-m-d') }}" readonly>
                                    </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Faktur Order</label>
                                            <input class="form-control bg-white" value="{{ $order->invoice_no }}" readonly />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tipe Pembayaran</label>
                                            <input class="form-control bg-white" value="{{ $order->paymentHistoryText() }}" readonly />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Jumlah Dibayar</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Rp</span>
                                                </div>
                                        <input type="text" class="form-control bg-white" value="{{ format_rupiah($order->pay_amount) }}" readonly>
                                        </div>
                                        </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Sisa Piutang</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">Rp</span>
                                                    </div>
                                        <input type="text" class="form-control bg-white" value="{{ format_rupiah(max($order->due_amount, 0)) }}" readonly>
                                        </div>
                                        </div>
                                        </div>
                                        </div>

                                        @php
                                            $invoiceExpired = $order->invoice_upload_status === 'uploaded'
                                                && $order->invoice_expires_at?->isPast();
                                            $invoiceUploaded = $order->invoice_upload_status === 'uploaded' && !$invoiceExpired;
                                        @endphp
                                        @if (!in_array($order->order_status, ['cancelled', 'void'], true) && (float) $order->pay_amount > 0)
                                        <div class="card border mt-4 mb-4">
                                            <div class="card-header">
                                                <h5 class="mb-0">INVOICE ELEKTRONIK</h5>
                                            </div>
                                            <div class="card-body">
                                                @if ($invoiceUploaded)
                                                    <p class="text-success mb-2"><strong>✓ Invoice tersedia</strong></p>
                                                    @if ($order->invoice_expires_at)
                                                        <p class="text-muted mb-3">Expired: {{ $order->invoice_expires_at->locale('id')->translatedFormat('d/m/Y H:i') }}</p>
                                                    @endif
                                                    <div class="d-flex flex-wrap">
                                                        <a href="{{ $order->invoice_url }}" target="_blank" rel="noopener" class="btn btn-primary mr-2 mb-2">
                                                            Buka Invoice
                                                        </a>
                                                        <button type="button" class="btn btn-outline-primary mr-2 mb-2 js-copy-invoice" data-invoice-url="{{ $order->invoice_url }}" aria-label="Salin link invoice">
                                                            Salin Link
                                                        </button>
                                                        <form action="{{ route('order.invoiceWhatsapp', $order->id) }}" method="POST" target="_blank" class="d-inline-block mb-2">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success">Kirim WhatsApp</button>
                                                        </form>
                                                    </div>
                                                @else
                                                    @if ($invoiceExpired)
                                                        <p class="text-warning mb-2"><strong>⌛ Invoice sudah expired</strong></p>
                                                    @elseif ($order->invoice_upload_status === 'failed')
                                                        <p class="text-danger mb-2"><strong>✕ Upload invoice gagal</strong></p>
                                                    @elseif ($order->invoice_upload_status === 'generated' || $order->invoice_upload_status === 'pending')
                                                        <p class="text-warning mb-2"><strong>⚠ Invoice belum di-upload</strong></p>
                                                    @else
                                                        <p class="text-warning mb-2"><strong>⚠ Invoice belum dibuat</strong></p>
                                                    @endif
                                                    @if ($order->invoice_error)
                                                        <p class="text-muted mb-3">{{ $order->invoice_error }}</p>
                                                    @endif
                                                    <div class="d-flex flex-wrap">
                                                        @if (!$order->invoice_pdf_path)
                                                            <form action="{{ route('order.invoiceGenerate', $order->id) }}" method="POST" class="mr-2 mb-2">
                                                                @csrf
                                                                <button type="submit" class="btn btn-primary">Generate Invoice</button>
                                                            </form>
                                                        @endif
                                                        <form action="{{ route('order.invoiceUpload', $order->id) }}" method="POST" class="mb-2">
                                                            @csrf
                                                            <button type="submit" class="btn btn-warning">Upload Invoice</button>
                                                        </form>
                                                        <form action="{{ route('order.invoiceWhatsapp', $order->id) }}" method="POST" target="_blank" class="d-inline-block mb-2">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success">Kirim WhatsApp + Invoice PDF</button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Actions for Pending Orders -->
                        @if ($order->order_status == 'pending')
                            <div class="row mt-4">
                                <div class="col-lg-12 d-flex justify-content-end">
                                    <form action="{{ route('order.updateStatus') }}" method="POST" class="d-inline">
                                        @method('put')
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $order->id }}">

                                        <button type="button" class="btn btn-outline-danger mr-2" data-toggle="modal" data-target="#cancelModal">
                                            <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Batalkan Order
                                        </button>

                                        <button type="submit" class="btn btn-success"
                                            onclick="return confirm('Apakah Anda yakin ingin menyelesaikan order ini? Stok akan dikurangi.')">
                                            <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Selesaikan Order
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
                                                <h5 class="modal-title" id="cancelModalLabel">Batalkan Order {{ $order->invoice_no }}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Alasan pembatalan <span class="text-danger">*</span></label>
                                                    <textarea name="cancel_reason" class="form-control" rows="3" required placeholder="Masukkan alasan pembatalan order ini..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                                <button type="submit" class="btn btn-danger">Konfirmasi Pembatalan</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @elseif ($order->order_status == 'complete')
                            <div class="row mt-3">
                                <div class="col-lg-12">
                                    <div class="alert alert-success text-center" role="alert">
                                        <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Order ini sudah selesai.
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
                                                        <strong>Peringatan:</strong> Void order ini akan mengembalikan semua stok produk. Aksi ini tidak bisa dibatalkan.
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Alasan void <span class="text-danger">*</span></label>
                                                        <textarea name="void_reason" class="form-control" rows="3" required placeholder="Masukkan alasan void order ini..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-dark">Konfirmasi Void</button>
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
                                        <x-heroicon-o-x-circle class="w-5 h-5 mr-1 inline" /> <strong>Order Dibatalkan</strong><br>
                                        Alasan: {{ $order->cancel_reason }}<br>
                                        <small>Dibatalkan oleh: {{ $order->cancelledBy->name ?? 'N/A' }} pada {{ $order->cancelled_at?->format('Y-m-d H:i') ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>
                        @elseif ($order->order_status == 'void')
                            <div class="row mt-3">
                                <div class="col-lg-12">
                                    <div class="alert alert-dark" role="alert">
                                        <x-heroicon-o-x-circle class="w-5 h-5 mr-1 inline" /> <strong>Order Di-void</strong><br>
                                        Alasan: {{ $order->void_reason }}<br>
                                        <small>Di-void oleh: {{ $order->voidedBy->name ?? 'N/A' }} pada {{ $order->voided_at?->format('Y-m-d H:i') ?? 'N/A' }}</small>
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
                        <h4 class="card-title">Item Order</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive rounded">
                            <table class="table mb-0">
                                <thead class="bg-light text-uppercase">
                                    <tr class="ligth ligth-data">
                                        <th>No.</th>
                                        <th>Foto</th>
                                        <th>Nama Produk</th>
                                        <th>Kode Produk</th>
                                        <th>Jumlah</th>
                                        <th>Harga</th>
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
                                            <td>{{ format_rupiah($item->unit_price) }}</td>
                                            <td>{{ format_rupiah($item->total) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="6" class="text-right font-weight-bold">Subtotal</td>
                                        <td class="font-weight-bold">{{ format_rupiah($order->sub_total) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right font-weight-bold">PPN</td>
                                        <td class="font-weight-bold">{{ format_rupiah($order->vat) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-right font-weight-bold text-primary" style="font-size: 1.1em;">
                                            Total</td>
                                        <td class="font-weight-bold text-primary" style="font-size: 1.1em;">
                                            {{ format_rupiah($order->total) }}
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

@section('specificpagescripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-copy-invoice').forEach(function (button) {
            button.addEventListener('click', async function () {
                const url = button.dataset.invoiceUrl;
                const status = document.getElementById('invoice-copy-status');

                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(url);
                    } else {
                        const input = document.createElement('textarea');
                        input.value = url;
                        input.setAttribute('readonly', '');
                        input.style.position = 'fixed';
                        input.style.opacity = '0';
                        document.body.appendChild(input);
                        input.select();
                        if (!document.execCommand('copy')) {
                            throw new Error('copy-failed');
                        }
                        input.remove();
                    }

                    status.textContent = 'Link invoice berhasil disalin.';
                    status.classList.remove('d-none', 'alert-danger');
                    status.classList.add('alert-info');
                } catch (error) {
                    status.textContent = 'Link invoice gagal disalin. Salin URL secara manual.';
                    status.classList.remove('d-none', 'alert-info');
                    status.classList.add('alert-danger');
                }
            });
        });
    });
</script>
@endsection
