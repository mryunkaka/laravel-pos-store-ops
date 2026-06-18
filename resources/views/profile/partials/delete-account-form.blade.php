<!-- begin: Delete Account -->
<div class="card-header d-flex justify-content-between">
    <div class="iq-header-title">
        <h4 class="card-title">Hapus Akun</h4>
    </div>
</div>
<div class="card-body">
    <div class="alert alert-warning" role="alert">
        <strong class="pr-2">Peringatan:</strong> Setelah akun Anda dihapus, semua data Anda akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.
    </div>

    <form action="{{ route('profile.destroy') }}" method="POST">
        @csrf
        @method('delete')
        <!-- begin: Input Data -->
        <div class="row align-items-center">
            <div class="form-group col-md-12">
                <label for="password">Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" id="password" name="password" required>
                @error('password', 'userDeletion')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
                <small class="form-text text-muted">Silakan masukkan password Anda untuk mengonfirmasi penghapusan akun.</small>
            </div>
        </div>
        <!-- end: Input Data -->
        <div class="mt-2">
            <button type="submit" class="btn btn-danger mr-2">Hapus Akun</button>
            <a class="btn bg-secondary" href="{{ route('profile') }}">Batal</a>
        </div>
    </form>
</div>
<!-- end: Delete Account -->
