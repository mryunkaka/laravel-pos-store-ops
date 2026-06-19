@extends('dashboard.body.main')

@section('title', 'Detail Stock Opname')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card inventory-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Stock Opname {{ $stockOpname->opname_number }}</h5>
                    <div class="inventory-actions mt-2 mt-sm-0">
                        <a href="{{ route('stock-opnames.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert text-white bg-success" role="alert">{{ session('success') }}</div>
                    @endif
                    @if (session()->has('error'))
                        <div class="alert text-white bg-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="alert alert-light border mb-0">
                                Tanggal<br><strong>{{ $stockOpname->opname_date->format('Y-m-d') }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-light border mb-0">
                                Status<br><strong>{{ ucfirst($stockOpname->status) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-light border mb-0">
                                Produk<br><strong>{{ $stockOpname->details->count() }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-light border mb-0">
                                Selisih Item<br><strong>{{ $stockOpname->details->where('difference', '!=', 0)->count() }}</strong>
                            </div>
                        </div>
                    </div>

                    @if ($stockOpname->isDraft())
                        <form action="{{ route('stock-opnames.counts.update', $stockOpname->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover inventory-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Stok Sistem</th>
                                    <th>Stok Fisik</th>
                                    <th>Selisih</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stockOpname->details as $detail)
                                    <tr>
                                        <td>{{ $detail->product->name ?? '-' }}</td>
                                        <td>{{ $detail->system_stock }}</td>
                                        <td>
                                            @if ($stockOpname->isDraft())
                                                <input type="number" name="details[{{ $detail->id }}][physical_stock]" class="form-control" min="0" value="{{ old('details.' . $detail->id . '.physical_stock', $detail->physical_stock) }}">
                                            @else
                                                {{ $detail->physical_stock }}
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $detail->difference == 0 ? 'secondary' : ($detail->difference > 0 ? 'success' : 'danger') }}">
                                                {{ $detail->difference }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($stockOpname->isDraft())
                                                <input type="text" name="details[{{ $detail->id }}][notes]" class="form-control" value="{{ old('details.' . $detail->id . '.notes', $detail->notes) }}">
                                            @else
                                                {{ $detail->notes ?? '-' }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="inventory-actions mt-3">
                        @if ($stockOpname->isDraft())
                            <button type="submit" class="btn btn-primary">Simpan Stok Fisik</button>
                        </form>
                            <a href="{{ route('stock-opnames.import', $stockOpname->id) }}" class="btn btn-secondary">Import Excel</a>
                            <form action="{{ route('stock-opnames.submit', $stockOpname->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-info">Submit Approval</button>
                            </form>
                            <form action="{{ route('stock-opnames.cancel', $stockOpname->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-danger">Batalkan</button>
                            </form>
                        @elseif ($stockOpname->isSubmitted())
                            <form action="{{ route('stock-opnames.approve', $stockOpname->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success">Approve dan Sesuaikan Stok</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
