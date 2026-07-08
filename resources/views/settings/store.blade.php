@extends('dashboard.body.main')

@section('title', 'Pengaturan Toko')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card inventory-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Pengaturan Toko</h5>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert text-white bg-success" role="alert">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert text-white bg-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('settings.store.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Nama Toko <span class="text-danger">*</span></label>
                                <input type="text" name="store_name" class="form-control @error('store_name') is-invalid @enderror" value="{{ old('store_name', $setting->store_name) }}" required>
                                @error('store_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label>Telepon</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $setting->phone) }}">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-12">
                                <label>Alamat</label>
                                <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $setting->address) }}</textarea>
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Pajak Default (%)</label>
                                <input type="number" name="default_tax_rate" class="form-control @error('default_tax_rate') is-invalid @enderror" value="{{ old('default_tax_rate', $setting->default_tax_rate) }}" min="0" max="100" step="0.01">
                                @error('default_tax_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Mata Uang <span class="text-danger">*</span></label>
                                <select name="currency" class="form-control @error('currency') is-invalid @enderror" required>
                                    @foreach(['IDR', 'USD', 'SGD', 'MYR'] as $currency)
                                        <option value="{{ $currency }}" {{ old('currency', $setting->currency) === $currency ? 'selected' : '' }}>{{ $currency }}</option>
                                    @endforeach
                                </select>
                                @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Logo</label>
                                <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                                @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <hr>
                        <h5 class="mb-3">WhatsApp Bot</h5>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Status WhatsApp</label>
                                <select name="whatsapp_enabled" class="form-control">
                                    <option value="0" {{ old('whatsapp_enabled', $setting->whatsapp_enabled) ? '' : 'selected' }}>Nonaktif</option>
                                    <option value="1" {{ old('whatsapp_enabled', $setting->whatsapp_enabled) ? 'selected' : '' }}>Aktif</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>API Version</label>
                                <input type="text" name="whatsapp_api_version" class="form-control @error('whatsapp_api_version') is-invalid @enderror" value="{{ old('whatsapp_api_version', $setting->whatsapp_api_version ?: 'v20.0') }}" placeholder="v20.0">
                                @error('whatsapp_api_version')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label>Phone Number ID</label>
                                <input type="text" name="whatsapp_phone_number_id" class="form-control @error('whatsapp_phone_number_id') is-invalid @enderror" value="{{ old('whatsapp_phone_number_id', $setting->whatsapp_phone_number_id) }}">
                                @error('whatsapp_phone_number_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-12">
                                <label>Access Token</label>
                                <input type="password" name="whatsapp_access_token" class="form-control @error('whatsapp_access_token') is-invalid @enderror" placeholder="{{ $setting->whatsapp_access_token ? 'Token sudah tersimpan. Isi hanya jika ingin mengganti.' : 'Masukkan permanent access token Meta' }}">
                                @error('whatsapp_access_token')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-12">
                                <label>Base URL Invoice Mobile</label>
                                <input type="url" name="whatsapp_invoice_base_url" class="form-control @error('whatsapp_invoice_base_url') is-invalid @enderror" value="{{ old('whatsapp_invoice_base_url', $setting->whatsapp_invoice_base_url ?: config('app.url')) }}" placeholder="https://domain-anda.com">
                                @error('whatsapp_invoice_base_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-12">
                                <label>Instruksi Transfer</label>
                                <textarea name="whatsapp_payment_instructions" class="form-control @error('whatsapp_payment_instructions') is-invalid @enderror" rows="7">{{ old('whatsapp_payment_instructions', $setting->whatsapp_payment_instructions ?: "TRANSFER PEMBAYARAN :\n\nBRI : 018001104535507\nBNI : 1918990066\nMANDIRI : 1590012252697\n\nAtas Nama : APONG MAMAH HALIMAH") }}</textarea>
                                @error('whatsapp_payment_instructions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end" style="gap: 8px;">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>

                    <hr>
                    <form method="POST" action="{{ route('settings.store.whatsappTest') }}">
                        @csrf
                        <div class="row align-items-end">
                            <div class="form-group col-md-8 mb-md-0">
                                <label>Nomor Test WhatsApp</label>
                                <input type="text" name="test_phone" class="form-control @error('test_phone') is-invalid @enderror" placeholder="Contoh: 628123456789">
                                @error('test_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4 mb-0 d-flex justify-content-md-end">
                                <button type="submit" class="btn btn-success">Kirim Test WhatsApp</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card inventory-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Preview</h5>
                </div>
                <div class="card-body text-center">
                    @if($setting->logo)
                        <img src="{{ asset('storage/' . $setting->logo) }}" alt="{{ $setting->store_name }}" class="img-fluid mb-3" style="max-height: 120px;">
                    @endif
                    <h5>{{ $setting->store_name }}</h5>
                    <p class="mb-1">{{ $setting->address ?: '-' }}</p>
                    <p class="mb-1">{{ $setting->phone ?: '-' }}</p>
                    <span class="badge bg-primary">{{ $setting->currency }}</span>
                    <span class="badge bg-info">Pajak {{ number_format($setting->default_tax_rate, 2) }}%</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
