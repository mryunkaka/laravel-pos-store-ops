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

                    <div id="update-notice" class="alert {{ $updateInfo['available'] ? 'alert-danger' : 'alert-secondary' }}" role="alert">
                        {{ $updateInfo['message'] }}
                    </div>

                    <div class="alert alert-danger" role="alert">
                        <strong>Aturan aman data:</strong> update web tidak boleh menghapus/mengubah isi data transaksi, piutang, produk, pelanggan, atau riwayat. Perubahan database hanya boleh tambah tabel, tambah kolom, tambah index, atau tambah permission. Jika butuh perubahan data lama, wajib backup dan konfirmasi manual dulu.
                    </div>

                    <form action="{{ route('system-update.run') }}" method="POST" onsubmit="return confirm('Jalankan update web sekarang?');">
                        @csrf
                        <button type="submit" class="btn btn-primary" {{ $isRunning ? 'disabled' : '' }}>
                            Jalankan Update Web
                        </button>
                    </form>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong id="update-progress-label">{{ $progress['label'] }}</strong>
                            <span id="update-progress-percent">{{ $progress['percent'] }}%</span>
                        </div>
                        <div class="progress" style="height: 24px;">
                            <div id="update-progress-bar" class="progress-bar progress-bar-striped {{ $isRunning ? 'progress-bar-animated' : '' }}" role="progressbar" style="width: {{ $progress['percent'] }}%;" aria-valuenow="{{ $progress['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                                {{ $progress['percent'] }}%
                            </div>
                        </div>
                        <small id="update-progress-note" class="text-muted d-block mt-2">{{ $isRunning ? 'Proses berjalan. Log diperbarui realtime.' : 'Siap.' }}</small>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Log Update</h5>
                        <div>
                            @if ($doneAt)
                                <small class="text-muted mr-2">Selesai terakhir: {{ $doneAt }}</small>
                            @endif
                            <button type="button" id="copy-update-log" class="btn btn-sm btn-outline-secondary">Copy Log Update</button>
                        </div>
                    </div>

                    <pre id="update-log" class="bg-dark text-white p-3 rounded" style="min-height: 320px; max-height: 520px; overflow: auto; white-space: pre-wrap;">{{ $log }}</pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var statusUrl = @json(route('system-update.status'));
        var logEl = document.getElementById('update-log');
        var labelEl = document.getElementById('update-progress-label');
        var percentEl = document.getElementById('update-progress-percent');
        var barEl = document.getElementById('update-progress-bar');
        var noteEl = document.getElementById('update-progress-note');
        var noticeEl = document.getElementById('update-notice');
        var copyLogBtn = document.getElementById('copy-update-log');
        var wasRunning = @json($isRunning);

        function setProgress(progress, running) {
            labelEl.textContent = progress.label;
            percentEl.textContent = progress.percent + '%';
            barEl.style.width = progress.percent + '%';
            barEl.setAttribute('aria-valuenow', progress.percent);
            barEl.textContent = progress.percent + '%';
            barEl.classList.toggle('progress-bar-animated', running);
        }

        function pollStatus() {
            fetch(statusUrl, { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    logEl.textContent = data.log;
                    logEl.scrollTop = logEl.scrollHeight;
                    setProgress(data.progress, data.running);
                    if (data.update_info) {
                        noticeEl.textContent = data.update_info.message;
                        noticeEl.className = 'alert ' + (data.update_info.available ? 'alert-danger' : 'alert-secondary');
                    }
                    noteEl.textContent = data.running ? 'Proses berjalan. Log diperbarui realtime.' : (data.error ? 'Update gagal. Cek log.' : 'Selesai atau siap.');

                    if (wasRunning && data.done && !data.error) {
                        window.location.reload();
                        return;
                    }

                    wasRunning = data.running || wasRunning;
                })
                .catch(function () {
                    noteEl.textContent = 'Gagal membaca status update. Halaman akan mencoba lagi.';
                });
        }

        copyLogBtn.addEventListener('click', function () {
            navigator.clipboard.writeText(logEl.textContent).then(function () {
                copyLogBtn.textContent = 'Log Disalin';
                setTimeout(function () { copyLogBtn.textContent = 'Copy Log Update'; }, 1500);
            }).catch(function () {
                copyLogBtn.textContent = 'Gagal Copy';
                setTimeout(function () { copyLogBtn.textContent = 'Copy Log Update'; }, 1500);
            });
        });

        pollStatus();
        setInterval(pollStatus, 1000);
    })();
</script>
@endsection
