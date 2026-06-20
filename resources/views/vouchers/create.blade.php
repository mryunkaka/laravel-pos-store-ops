@extends('dashboard.body.main')

@section('title', 'Tambah Voucher')

@section('container')
<div class="container-fluid">
    <div class="card inventory-card">
        <div class="card-header"><h5 class="card-title mb-0">Tambah Voucher</h5></div>
        <div class="card-body">
            <form action="{{ route('vouchers.store') }}" method="POST">
                @include('vouchers._form', ['voucher' => null, 'submitLabel' => 'Simpan'])
            </form>
        </div>
    </div>
</div>
@endsection
