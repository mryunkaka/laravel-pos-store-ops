@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                {{-- Alert: Validation Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Bayar Semua Gaji (Pembayaran Massal)</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Information Alert --}}
                        <div class="alert alert-warning">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 mr-2 inline" />
                            <strong>Perhatian!</strong> Ini akan memeriksa semua karyawan. Jika mereka belum dibayar untuk bulan yang dipilih, catatan pembayaran akan dibuat. Gaji di Muka yang disetujui untuk bulan tersebut akan dipotong secara otomatis.
                        </div>

                        <form action="{{ route('pay-salary.payAllStore') }}" method="POST">
                            @csrf
                            <div class="row align-items-center">
                                {{-- Month Selection --}}
                                <div class="form-group col-md-6">
                                    <label for="month">Bulan Gaji <span class="text-danger">*</span></label>
                                    <select class="form-control" name="month" required>
                                        <option value="" disabled selected>Pilih Bulan</option>
                                        @foreach(range(1, 12) as $m)
                                            <option value="{{ sprintf('%02d', $m) }}" {{ old('month') == sprintf('%02d', $m) ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Year Selection --}}
                                <div class="form-group col-md-6">
                                    <label for="year">Tahun Gaji <span class="text-danger">*</span></label>
                                    <select class="form-control" name="year" required>
                                        <option value="{{ date('Y') }}" {{ old('year') == date('Y') ? 'selected' : '' }}>{{ date('Y') }}</option>
                                        <option value="{{ date('Y') - 1 }}" {{ old('year') == date('Y') - 1 ? 'selected' : '' }}>{{ date('Y') - 1 }}</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary mr-2" onclick="return confirm('Apakah Anda yakin ingin memproses pembayaran gaji untuk SEMUA karyawan bulan ini?')">
                                    <x-heroicon-o-currency-dollar class="w-5 h-5 mr-1 inline" /> Proses Semua Pembayaran
                                </button>
                                <a class="btn btn-secondary" href="{{ route('pay-salary.index') }}">
                                    <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
