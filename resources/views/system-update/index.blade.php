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

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-1">Update Web</h4>
                        <p class="mb-0 text-muted">Update lokal 1 klik: git pull, composer install, npm build, migrate, dan cache ulang.</p>
                    </div>
                    <span class="badge {{ $isRunning ? 'badge-warning' : 'badge-success' }}">
                        {{ $isRunning ? 'Sedang Berjalan' : 'Siap' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning" role="alert">
                        <strong>Penting:</strong> jangan tutup server saat proses berjalan. Pastikan perubahan lokal sudah commit/stash agar `git pull --ff-only` tidak gagal.
                    </div>

                    <div class="alert alert-info" role="alert">
                        Link darurat jika GUI/sidebar tidak bisa dibuka: <a href="{{ route('system-update.test') }}" target="_blank">{{ url('/update-web.test') }}</a>
                    </div>

                    <form action="{{ route('system-update.run') }}" method="POST" onsubmit="return confirm('Jalankan update web sekarang?');">
                        @csrf
                        <button type="submit" class="btn btn-primary" {{ $isRunning ? 'disabled' : '' }}>
                            Jalankan Update Web
                        </button>
                    </form>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Log Update</h5>
                        @if ($doneAt)
                            <small class="text-muted">Selesai terakhir: {{ $doneAt }}</small>
                        @endif
                    </div>

                    <pre class="bg-dark text-white p-3 rounded" style="min-height: 320px; max-height: 520px; overflow: auto; white-space: pre-wrap;">{{ $log }}</pre>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($isRunning)
    <script>
        setTimeout(function () {
            window.location.reload();
        }, 3000);
    </script>
@endif
@endsection
