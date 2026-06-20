@extends('dashboard.body.main')

@section('title', 'Edit Voucher')

@section('container')
<div class="container-fluid">
    <div class="card inventory-card">
        <div class="card-header"><h5 class="card-title mb-0">Edit Voucher</h5></div>
        <div class="card-body">
            <form action="{{ route('vouchers.update', $voucher->id) }}" method="POST">
                @method('PUT')
                @include('vouchers._form', ['voucher' => $voucher, 'submitLabel' => 'Update'])
            </form>
        </div>
    </div>
</div>
@endsection
