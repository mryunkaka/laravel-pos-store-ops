<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scanner Barcode Kasir</title>
    <style>
        body { margin: 0; padding: 16px; font-family: Arial, sans-serif; background: #f4f6f8; color: #1f2937; }
        main { max-width: 560px; margin: 0 auto; }
        video { width: 100%; max-height: 70vh; object-fit: cover; border-radius: 12px; background: #111827; }
        button { border: 0; border-radius: 8px; padding: 12px 16px; color: #fff; background: #198754; font-size: 16px; }
        button.secondary { background: #6c757d; }
        button.confirm { background: #0d6efd; }
        .d-none { display: none !important; }
        .actions { display: flex; gap: 8px; margin: 12px 0; flex-wrap: wrap; }
        .status { min-height: 24px; color: #198754; }
        .hint { color: #6b7280; }
        .confirmation { padding: 14px; margin: 12px 0; border: 1px solid #dbe3ea; border-radius: 10px; background: #fff; }
        .confirmation strong { display: block; font-size: 18px; margin-bottom: 6px; }
        .price { font-size: 22px; font-weight: 700; color: #0d6efd; }
    </style>
</head>
<body>
    <main>
        <h2>Scanner Barcode Kasir</h2>
        <p class="hint">Arahkan kamera HP ke barcode. Setelah konfirmasi harga, HP kembali standby scan berikutnya.</p>
        <div class="actions">
            <button type="button" id="start_remote_barcode_camera">Mulai Kamera</button>
            <button type="button" id="close_remote_barcode_camera" class="secondary d-none">Tutup Kamera</button>
        </div>
        <video id="remote_barcode_camera_video" class="d-none" playsinline muted></video>
        <input type="hidden" id="remote_barcode_value">
        <div id="remote_barcode_confirmation" class="confirmation d-none">
            <strong id="remote_product_name"></strong>
            <div id="remote_product_code"></div>
            <div id="remote_product_stock"></div>
            <div id="remote_product_price" class="price"></div>
            <div class="actions">
                <button type="button" id="confirm_remote_barcode" class="confirm">Konfirmasi Harga</button>
                <button type="button" id="cancel_remote_barcode" class="secondary">Scan Lagi</button>
            </div>
        </div>
        <p id="remote_barcode_camera_status" class="status">Kamera belum aktif.</p>
    </main>

    <script src="{{ asset('assets/js/barcode-camera.js') }}"></script>
    <script>
        const lookupUrl = @json($lookupUrl);
        const scanUrl = @json($scanUrl);
        const csrfToken = @json(csrf_token());
        let pendingCode = '';
        let barcodeScanner = null;

        const confirmation = document.getElementById('remote_barcode_confirmation');
        const status = document.getElementById('remote_barcode_camera_status');

        function setRemoteStatus(message, isError) {
            status.textContent = message;
            status.style.color = isError ? '#dc3545' : '#198754';
        }

        function hideConfirmation() {
            confirmation.classList.add('d-none');
            pendingCode = '';
        }

        async function showProductConfirmation(code) {
            pendingCode = code;
            barcodeScanner.pause();
            setRemoteStatus('Barcode terbaca. Memeriksa produk dan harga...', false);

            try {
                const response = await fetch(lookupUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ code: code })
                });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.message || 'Produk tidak ditemukan.');

                document.getElementById('remote_product_name').textContent = data.product.name;
                document.getElementById('remote_product_code').textContent = 'Kode: ' + data.product.code;
                document.getElementById('remote_product_stock').textContent = 'Stok: ' + data.product.stock;
                document.getElementById('remote_product_price').textContent = new Intl.NumberFormat('id-ID', {
                    style: 'currency', currency: 'IDR', maximumFractionDigits: 0
                }).format(data.product.price);
                confirmation.classList.remove('d-none');
                setRemoteStatus('Periksa harga, lalu tekan Konfirmasi Harga.', false);
            } catch (error) {
                setRemoteStatus(error.message || 'Produk gagal diperiksa.', true);
                barcodeScanner.resume();
            }
        }

        async function confirmBarcode() {
            if (!pendingCode) return;
            const button = document.getElementById('confirm_remote_barcode');
            button.disabled = true;

            try {
                const response = await fetch(scanUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ code: pendingCode })
                });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.message || 'Barcode gagal dikirim.');
                hideConfirmation();
                setRemoteStatus('Berhasil dikirim ke kasir. Siap scan berikutnya.', false);
                barcodeScanner.resume();
            } catch (error) {
                setRemoteStatus(error.message || 'Barcode gagal dikirim ke kasir.', true);
            } finally {
                button.disabled = false;
            }
        }

        document.getElementById('confirm_remote_barcode').addEventListener('click', confirmBarcode);
        document.getElementById('cancel_remote_barcode').addEventListener('click', () => {
            hideConfirmation();
            setRemoteStatus('Siap scan barcode berikutnya.', false);
            barcodeScanner.resume();
        });

        barcodeScanner = createBarcodeCameraScanner({
            buttonId: 'start_remote_barcode_camera',
            videoId: 'remote_barcode_camera_video',
            statusId: 'remote_barcode_camera_status',
            inputId: 'remote_barcode_value',
            closeId: 'close_remote_barcode_camera',
            soundUrl: '{{ asset('assets/audio/store-scanner-beep-90395.mp3') }}',
            keepOpenOnDetected: true,
            onDetected: showProductConfirmation
        });
    </script>
</body>
</html>