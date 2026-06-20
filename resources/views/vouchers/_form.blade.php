@csrf
<div class="row">
    <div class="col-md-4 form-group">
        <label>Kode</label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $voucher->code ?? '') }}" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8 form-group">
        <label>Nama</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $voucher->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 form-group">
        <label>Tipe</label>
        <select name="type" class="form-control">
            <option value="fixed" {{ old('type', $voucher->type ?? 'fixed') == 'fixed' ? 'selected' : '' }}>Nominal</option>
            <option value="percentage" {{ old('type', $voucher->type ?? '') == 'percentage' ? 'selected' : '' }}>Persen</option>
        </select>
    </div>
    <div class="col-md-4 form-group">
        <label>Diskon</label>
        <input type="number" name="discount" class="form-control @error('discount') is-invalid @enderror" value="{{ old('discount', $voucher->discount ?? 0) }}" min="0" step="0.01" required>
        @error('discount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 form-group">
        <label>Maks Diskon</label>
        <input type="number" name="max_discount" class="form-control" value="{{ old('max_discount', $voucher->max_discount ?? '') }}" min="0">
    </div>
    <div class="col-md-4 form-group">
        <label>Minimal Belanja</label>
        <input type="number" name="min_purchase" class="form-control" value="{{ old('min_purchase', $voucher->min_purchase ?? 0) }}" min="0">
    </div>
    <div class="col-md-4 form-group">
        <label>Tanggal Mulai</label>
        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', isset($voucher) ? $voucher->start_date->format('Y-m-d') : now()->toDateString()) }}" required>
    </div>
    <div class="col-md-4 form-group">
        <label>Tanggal Berakhir</label>
        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', isset($voucher) ? $voucher->end_date->format('Y-m-d') : now()->addMonth()->toDateString()) }}" required>
    </div>
    <div class="col-md-4 form-group">
        <label>Maks Penggunaan</label>
        <input type="number" name="max_use" class="form-control" value="{{ old('max_use', $voucher->max_use ?? '') }}" min="1">
    </div>
    <div class="col-md-8 form-group">
        <label>Deskripsi</label>
        <input type="text" name="description" class="form-control" value="{{ old('description', $voucher->description ?? '') }}">
    </div>
    <div class="col-md-12 form-group">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $voucher->is_active ?? true) ? 'checked' : '' }}>
            <label class="custom-control-label" for="is_active">Aktif</label>
        </div>
    </div>
</div>
<div class="inventory-actions">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('vouchers.index') }}" class="btn btn-secondary">Batal</a>
</div>
