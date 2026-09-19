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
                            <h4 class="card-title">Bayar Gaji</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <strong>Pembayaran gaji gagal.</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('pay-salary.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $advanceSalary->id }}">

                            <div class="row align-items-center">
                                {{-- Section: Employee Information --}}
                                <div class="form-group col-md-6">
                                    <label>Nama Karyawan</label>
                                    <input type="text" class="form-control bg-white" value="{{ $advanceSalary->employee->name }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Bulan</label>
                                    <input type="text" class="form-control bg-white" value="{{ $advanceSalary->date }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Gaji</label>
                                    <input type="text" class="form-control bg-white" value="{{ format_rupiah($advanceSalary->employee->salary) }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Gaji di Muka</label>
                                    <input type="text" class="form-control bg-white" value="{{ format_rupiah($advanceSalary->advance_salary) }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Gaji Terhutang</label>
                                    <input type="text" class="form-control bg-white"
                                        value="{{ format_rupiah($advanceSalary->employee->salary - $advanceSalary->advance_salary) }}" readonly>
                                </div>

                                {{-- Section: Salary Month Selection --}}
                                <div class="form-group col-md-4">
                                    <label for="month">Bulan Gaji <span class="text-danger">*</span></label>
                                    <select class="form-control @error('month') is-invalid @enderror" name="month" required>
                                        <option value="" disabled>Pilih Bulan</option>
                                        @foreach ([
                                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                                        ] as $monthValue => $monthLabel)
                                            <option value="{{ $monthValue }}" {{ old('month', $advanceMonth) === $monthValue ? 'selected' : '' }}>{{ $monthLabel }}</option>
                                        @endforeach
                                    </select>
                                    @error('month')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="year">Tahun Gaji <span class="text-danger">*</span></label>
                                    <select class="form-control @error('year') is-invalid @enderror" name="year" required>
                                        <option value="{{ date('Y') }}" {{ old('year', $advanceYear) == date('Y') ? 'selected' : '' }}>{{ date('Y') }}</option>
                                        <option value="{{ date('Y') - 1 }}" {{ old('year', $advanceYear) == date('Y') - 1 ? 'selected' : '' }}>{{ date('Y') - 1 }}</option>
                                    </select>
                                    @error('year')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Section: Payment Date --}}
                                <div class="form-group col-md-4">
                                    <label for="datepicker">Tanggal Pembayaran <span class="text-danger">*</span></label>
                                    <input id="datepicker" class="form-control @error('date') is-invalid @enderror" name="date"
                                        value="{{ old('date', date('Y-m-d')) }}" />
                                    @error('date')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Section: Form Actions --}}
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Bayar Gaji
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

    {{-- Script: Datepicker Initialization --}}
    <script>
        $('#datepicker').datepicker({
            uiLibrary: 'bootstrap4',
            format: 'yyyy-mm-dd'
        });
    </script>
@endsection
